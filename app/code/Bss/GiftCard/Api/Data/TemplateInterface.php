<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCard\Api\Data;

/**
 * Interface TemplateInterface
 *
 * Bss\GiftCard\Api\Data
 */
interface TemplateInterface
{
    public const ID = 'template_id';

    public const NAME = 'name';

    public const STATUS = 'status';
    public const CODE_COLOR = 'code_color';
    public const MESSAGE_COLOR = 'message_color';

    public const CREATED_AT = 'created_time';
    public const UPDATED_AT = 'updated_time';

    /**
     * Get template identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Set template identifier
     *
     * @param int $val
     * @return $this
     */
    public function setId($val);

    /**
     * Get template name
     *
     * @return string
     */
    public function getName();

    /**
     * Set template name
     *
     * @param string $val
     * @return $this
     */
    public function setName($val);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $val
     * @return mixed
     */
    public function setStatus($val);

    /**
     * Get code color
     *
     * @return string
     */
    public function getCodeColor();

    /**
     * Set code color
     *
     * @param string $val
     * @return $this
     */
    public function setCodeColor($val);

    /**
     * Get message color
     *
     * @return string
     */
    public function getMessageColor();

    /**
     * Set message color
     *
     * @param string $val
     * @return $this
     */
    public function setMessageColor($val);

    /**
     * Get create time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set create time
     *
     * @param string $val
     * @return $this
     */
    public function setCreatedAt($val);

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $val
     * @return $this
     */
    public function setUpdatedAt($val);
}
