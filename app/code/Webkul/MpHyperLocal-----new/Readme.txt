#Installation

Magento2 Marketplace Hyperlocal module installation is very easy, please follow the steps for installation.

As Marketplace Hyperlocal module is a bundle of few extensions, so will need to install those extensions first.

Step 1 - Installation of Webkul Magento2 MpMSI Module, for this extract MpMSI extension zip and follow it's readme.txt file installation steps.

Step 2 - Unzip the MpHyperlocal extension zip and create Webkul(vendor) and MpHyperlocal(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MpHyperlocal/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#User Guide

For Magento2 Marketplace Hyperlocal module's working process follow user guide - http://webkul.com/blog/magento2-marketplace-hyperlocal-system/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
