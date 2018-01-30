<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $errors = $this->get('validator')->validateValue(array('file' => $file), $fileConstraint);
        if ($errors->count() > 0) {
            return new JsonResponse(['msg' => 'Your file is not valid!'], 400);
        }

        $uploadDir = $this->container->getParameter('upload_dir');

        // Move uploaded file
        $newFileName = uniqid().'.'.$file->guessExtension();
        $path = $this->container->getParameter('kernel.root_dir')."/../web".$uploadDir;
        try {
            $file->move($path, $newFileName);
        } catch (FileException $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        }

        // Get image width/height
        list($width, $height) = getimagesize(
            $path.DIRECTORY_SEPARATOR.$newFileName
        );

        return new JsonResponse(
            $response = [
                'status' => 'success',
                'src' => $uploadDir.'/'.$newFileName,
                'width' => $width,
                'height' => $height,
            ]
        );
    }
}
