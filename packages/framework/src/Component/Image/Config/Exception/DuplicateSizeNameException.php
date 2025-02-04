<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateSizeNameException extends Exception implements ImageConfigException
{
    protected ?string $sizeName = null;

    /**
     * @param string|null $sizeName
     * @param \Exception|null $previous
     */
    public function __construct($sizeName = null, ?Exception $previous = null)
    {
        $this->sizeName = $sizeName;

        if ($this->sizeName === null) {
            $message = 'Image size NULL is not unique.';
        } else {
            $message = sprintf('Image size "%s" is not unique.', $this->sizeName);
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string|null
     */
    public function getSizeName()
    {
        return $this->sizeName;
    }
}
