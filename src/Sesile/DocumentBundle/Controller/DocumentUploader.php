<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 07/01/14
 * Time: 09:21
 */

namespace Sesile\DocumentBundle\Controller;


use Oneup\UploaderBundle\Controller\AbstractController;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\PostUploadEvent;
use Oneup\UploaderBundle\Event\PreUploadEvent;
use Oneup\UploaderBundle\Event\ValidationEvent;
use Oneup\UploaderBundle\Uploader\ErrorHandler\ErrorHandlerInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Oneup\UploaderBundle\Uploader\Response\EmptyResponse;
use Oneup\UploaderBundle\Uploader\Response\ResponseInterface;
use Oneup\UploaderBundle\Uploader\Storage\StorageInterface;
use Oneup\UploaderBundle\UploadEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\FileBag;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;


class DocumentUploader extends AbstractController
{


    protected $container;
    protected $storage;
    protected $config;
    protected $type;
    protected $lastfilename;


    /**
     *  This internal function handles the actual upload process
     *  and will most likely be called from the upload()
     *  function in the implemented Controller.
     *
     *  Note: The return value differs when
     *
     * @param The file to upload
     * @param response A response object.
     * @param request The request object.
     */
    protected function handleUpload($file, ResponseInterface $response, Request $request)
    {
        // wrap the file if it is not done yet which can only happen
        // if it wasn't a chunked upload, in which case it is definitely
        // on the local filesystem.
        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }
        $this->validate($file);

        $this->dispatchPreUploadEvent($file, $response, $request);

        // no error happend, proceed
        $namer = $this->container->get($this->config['namer']);
        $name = $namer->name($file);
        $this->lastfilename = $name;

        // perform the real upload
        $uploaded = $this->storage->upload($file, $name);

        $this->dispatchPostEvents($uploaded, $response, $request);
    }

    public function upload()
    {
        // get some basic stuff together
//        $request = $this->container->get('request');
//        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request = $this->getRequest();

        $response = array();
        $emptyResponse = new EmptyResponse();
        $files = $this->getFiles($request->files);

        foreach ($files as $file) {
            try {
                $this->handleUpload($file, $emptyResponse, $request);
                $response[$this->lastfilename] = $file->getClientOriginalName();

            } catch (UploadException $e) {
                $this->errorHandler->addException($response, $e);
            }
        }

        return new JsonResponse($response);
    }
} 