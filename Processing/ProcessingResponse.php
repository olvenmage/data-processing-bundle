<?php

namespace Olveneer\DataProcessorBundle\Processing;

/**
 * Class ProcessingResponse
 * @package App\Service\API
 * @author Douwe
 */
class ProcessingResponse implements \JsonSerializable
{
    const STATUS_INVALID_DATA =     'INVALID_DATA';
    const STATUS_OK =               'OK';
    const STATUS_FORM_ERROR =       'FORM_ERROR';

    /**
     * @var array
     */
    public $messages;

    /**
     * @var string
     */
    public $status;

    /**
     * ProcessingResponse constructor.
     * @param array $messages
     * @param string $status
     */
    public function __construct(string $status = '', array $messages = [])
    {
        $this->messages = $messages;
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->status === self::STATUS_OK;
    }

    /**
     * @return bool
     */
    public function isInvalidData()
    {
        return $this->status === self::STATUS_INVALID_DATA;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     * @return ProcessingResponse
     */
    public function setMessages(array $messages): ProcessingResponse
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ProcessingResponse
     */
    public function setStatus(string $status): ProcessingResponse
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'messages' => $this->messages
        ];
    }
}
