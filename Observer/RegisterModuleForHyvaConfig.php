<?php
declare(strict_types=1);

namespace Foundation\Cms\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RegisterModuleForHyvaConfig
 *
 * @package  Foundation\Cms\Observer
 */
class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * RegisterModuleForHyvaConfig constructor.
     *
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Register module for tailwind styling
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $config = $observer->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];
        $moduleName = implode('_', array_slice(explode('\\', __CLASS__), 0, 2));
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);

        $extensions[] = [
            'src' => substr($path, strlen(BP) + 1)
        ];

        $config->setData('extensions', $extensions);
    }
}
