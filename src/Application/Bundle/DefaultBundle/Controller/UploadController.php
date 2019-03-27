<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UploadController.
 */
class UploadController extends Controller
{
    /**
     * Upload image (for markitup plugin).
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @Route("/admin/text-area/uploadImage", name="text_area_upload_image")
     *
     * @Method({"POST"})
     */
    public function uploadImageAction(Request $request)
    {
        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile|null */
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
        /** @var $errors \Symfony\Component\Validator\ConstraintViolationList */
        $errors = $this->get('validator')->validateValue(['file' => $file], $fileConstraint);
        if ($errors->count() > 0) {
            return new JsonResponse(['msg' => 'Your file is not valid!'], 400);
        }
        $newFileName = uniqid().'.'.$file->guessExtension();

        $adapter = $this->get('oneup_flysystem.upload_image_filesystem')->getAdapter();

        try {
            $newFile = $this->uploadFile($file->getPathname(), $adapter->getPathPrefix().$newFileName);
            $source = $newFile;
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        }

        // Get image width/height
        list($width, $height) = getimagesize($newFile);

        return new JsonResponse(
            $response = [
                'status' => 'success',
                'src' => $source,
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
