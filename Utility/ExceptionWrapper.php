<?php

namespace Osm\EasyRestBundle\Utility;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ExceptionWrapper
{
    /**
     * @var int
     */
    protected $code = 0;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_NOT_FOUND;

    /**
     * @var array
     */
    protected $trace = array();

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param $trace
     * @return $this
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;

        return $this;
    }

    public function setErrorsFromConstraintViolations(ConstraintViolationListInterface $errors)
    {
        $this->errors = [];
        foreach ($errors as $error) {
            $this->addError(
                $error->getPropertyPath(),
                $error->getMessage()
            );
        }

        return $this;
    }

    public function addError($path, $message)
    {
        if (strpos($path, '[') !== false) {
            $path = substr($path, 1, -1);
        }
        array_push(
            $this->errors,
            array(
                'path' => $path,
                'message' => $message,
            )
        );

        return $this;
    }

    /**
     * @return string
     */
    private function getStatusTextFromCode()
    {
        if (isset(Response::$statusTexts[$this->statusCode])) {
            return Response::$statusTexts[$this->statusCode];
        }

        return '';
    }

    public function getResponse()
    {
        return new JsonResponse(
            array(
                'status_code' => $this->statusCode,
                'status_text' => $this->getStatusTextFromCode(),
                'code' => $this->code,
                'message' => $this->message,
                'errors' => $this->errors,
                'trace' => $this->trace,
            ),
            $this->statusCode
        );
    }
}
