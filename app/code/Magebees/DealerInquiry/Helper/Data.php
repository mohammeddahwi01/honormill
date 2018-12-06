<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Captcha image model
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * Captcha fonts path
     */
    const XML_PATH_CAPTCHA_FONTS = 'captcha/fonts';

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_sessionManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        \Magento\Customer\Model\Session $sessionManager,
        \Magento\Captcha\Helper\Data $captchaHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_sessionManager = $sessionManager;
        $this->_captchaHelper = $captchaHelper;
        parent::__construct($context);
    }

    
    /**
     * Get captcha image directory
     *
     * @param mixed $website
     * @return string
     */
    public function getImgDir($website = null)
    {
        $mediaDir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $captchaDir = '/inquirycaptcha/' . $this->_getWebsiteCode($website);
        $mediaDir->create($captchaDir);
        $mediaDir->changePermissions($captchaDir, 0755);

        return $mediaDir->getAbsolutePath($captchaDir) . '/';
    }

    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     */
    protected function _getWebsiteCode($website = null)
    {
        return $this->_storeManager->getWebsite($website)->getCode();
    }

    /**
     * Get captcha image base URL
     *
     * @param mixed $website
     * @return string
     */
    public function getImgUrl($website = null)
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            DirectoryList::MEDIA
        ) . 'inquirycaptcha' . '/' . $this->_getWebsiteCode(
            $website
        ) . '/';
    }
    
    public function createCaptchaImage()
    {
        $word="";
        $image = imagecreatetruecolor(130, 50);
                
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 200, 50, $background_color);
        $line_color = imagecolorallocate($image, 64, 64, 64);
                
        for ($i=0; $i<10; $i++) {
            imageline($image, 0, rand()%50, 200, rand()%50, $line_color);
        }
        
        $pixel_color = imagecolorallocate($image, 0, 0, 255);
        for ($i=0; $i<1000; $i++) {
            imagesetpixel($image, rand()%200, rand()%50, $pixel_color);
        }
        
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $len = strlen($letters);
        $letter = $letters[rand(0, $len-1)];
        $fonts = $this->_captchaHelper->getFonts();
        $font = $fonts['linlibertine']['path'];
        $text_color = imagecolorallocate($image, 0, 0, 0);

        for ($i = 0; $i< 4; $i++) {
            $letter = $letters[rand(0, $len-1)];
            imagettftext($image, 25, 0, 5+($i*32), 38, $text_color, $font, $letter);
            $word.=$letter;
        }
                        
        $this->_sessionManager->setInquiryCaptcha($word);//save captcha to session
        
        $path = $this->getImgDir(); //Directory path of captcha image
                        
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
                
        $random = rand();
        $image_name = "captcha-".$random.".png";
        
        imagepng($image, $path."/".$image_name);//captcha imge at specified directory
        
        return $image_name;
    }
    
    public function getOwnerEmail()
    {
        $send_to = $this->scopeConfig->getValue('inquiry/admin_email/send_to', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($send_to == "custom") {
            $adminEmail = $this->scopeConfig->getValue('inquiry/admin_email/owner_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            ;
            if (empty($adminEmail)) {
                $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
        } else {
            $adminEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $adminEmail;
    }
}
