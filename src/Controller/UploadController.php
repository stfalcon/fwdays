<?php

namespace App\Controller;

use App\Traits\ValidatorTrait;
use Aws\S3\S3Client;
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

    private $s3Client;
    private $uploadImagePath;
    private $awsS3BucketName;
    private $awsS3PublicEndpoint;

    /**
     * @param S3Client $s3Client
     * @param string   $uploadImagePath
     * @param string   $awsS3BucketName
     * @param string   $awsS3PublicEndpoint
     */
    public function __construct(S3Client $s3Client, string $uploadImagePath, string $awsS3BucketName, string $awsS3PublicEndpoint)
    {
        $this->uploadImagePath = $uploadImagePath;
        $this->s3Client = $s3Client;
        $this->awsS3BucketName = $awsS3BucketName;
        $this->awsS3PublicEndpoint = $awsS3PublicEndpoint;
    }

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
        $realPath = \is_string($file->getRealPath()) ? $file->getRealPath() : '';
        $pathToFile = \sprintf('%s/%s.%s', $this->uploadImagePath, \md5_file($realPath), $extension);
        try {
            $this->uploadFile($file->getPathname(), $pathToFile);
            $fullFileUrl = \sprintf('%s/%s', $this->awsS3PublicEndpoint, $pathToFile);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        }

        return new JsonResponse(
            [
                'status' => 'success',
                'src' => $fullFileUrl,
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
            if (false !== $mimeTypeHandler) {
                $meta['contentType'] = \finfo_file($mimeTypeHandler, $fileName);
                \finfo_close($mimeTypeHandler);
            }
        }
        $content = \file_get_contents($fileName);
        if (!\is_string($content)) {
            $content = '';
        }

        return $this->upload($newFilename, $content, $meta, $privacy);
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
        return $this->s3Client->upload($this->awsS3BucketName, $fileName, $content, $privacy, [
            'Metadata' => $meta,
        ])->toArray()['ObjectURL'];
    }
}
