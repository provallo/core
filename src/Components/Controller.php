<?php

namespace ProVallo\Components;

use Favez\Mvc\Hook\Hookable;

/**
 * Class Controller
 *
 * @package CMS\Components
 *
 * @method static \ProVallo\Components\Plugin\Manager   plugins()
 */
abstract class Controller extends \Favez\Mvc\Controller implements Hookable
{

}