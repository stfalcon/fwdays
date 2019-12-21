<?php

namespace App\Controller;

use App\Traits\ValidatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * UploadController.
 */
class UploadController extends AbstractController
{
    use ValidatorTrait;

    /**
     * @Route("/admin/text-area/uploadImage", name="text_area_upload_image", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadImageAction(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('upload_file');

        $fileConstraint = new Collection(
            [
                'file' => [
                    new NotBlank(),
                    new Image(),
                ],
            ]
        );

        // Validate
        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate(['file' => $file], $fileConstraint);
        if ($errors->count() > 0) {
            return new JsonResponse(['msg' => 'Your file is not valid!'], 400);
        }
        list($width, $height) = \getimagesize($file);
        $extension = $file->guessExtension() ? $file->guessExtension() : $file->getClientOriginalExtension();
        $newFileName = \md5_file($file->getRealPath()).'.'.$extension;

        $adapter = $this->get('oneup_flysystem.upload_image_filesystem')->getAdapter();

        try {
            $this->uploadFile($file->getPathname(), $adapter->getPathPrefix().$newFileName);
            $newFile = $this->getParameter('aws_s3_public_endpoint').'/'.$adapter->getPathPrefix().$newFileName;
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        }

        return new JsonResponse(
            $response = [
                'status' => 'success',
                'src' => $newFile,
                'width' => $width,
                'height' => $height,
            ]
        );
    }

    /**
     * @param string      $fileName
     * @param string|null $newFilename
     * @param array       $meta
     * @param string      $privacy
     *
     * @return string file url
     */
    public function uploadFile($fileName, $newFilename = null, array $meta = [], $privacy = 'public-read')
    {
        if (!$newFilename) {
            $newFilename = basename($fileName);
        }
        if (!isset($meta['contentType'])) {
            $mimeTypeHandler = \finfo_open(FILEINFO_MIME_TYPE);
            $meta['contentType'] = \finfo_file($mimeTypeHandler, $fileName);
            \finfo_close($mimeTypeHandler);
        }

        return $this->upload($newFilename, \file_get_contents($fileName), $meta, $privacy);
    }

    /**
     * @param string $fileName
     * @param string $content
     * @param array  $meta
     * @param string $privacy
     *
     * @return string file url
     */
    public function upload($fileName, $content, array $meta = [], $privacy = 'public-read')
    {
        $s3client = $this->get('app.assets.s3');
        $bucket = $this->getParameter('aws_s3_bucketname');

        return $s3client->upload($bucket, $fileName, $content, $privacy, [
            'Metadata' => $meta,
        ])->toArray()['ObjectURL'];
    }
}
