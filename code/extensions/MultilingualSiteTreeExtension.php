<?php
class MultilingualSiteTreeExtension extends DataExtension {
    /**
     * Updates the CMS fields adding the fields defined in this extension
     * @param {FieldList} $fields Field List that new fields will be added to
     */
    public function updateCMSFields(FieldList $fields) {
        $urlSegmentField=$fields->dataFieldByName('URLSegment');
        if($urlSegmentField) {
            $baseLink=Controller::join_links(
                                            Director::absoluteBaseURL(),
                                            (MultilingualRootURLController::config()->UseLocaleURL ? (MultilingualRootURLController::config()->UseDashLocale ? str_replace('_', '-', strtolower($this->owner->Locale)):$this->owner->Locale):i18n::get_lang_from_locale($this->owner->Locale)).'/',
                                            (SiteTree::config()->nested_urls && $this->owner->ParentID ? $this->owner->Parent()->RelativeLink(true):null)
                                        );
            
            
            $urlSegmentField->setURLPrefix($baseLink);
        }
    }
    
    /**
     * Gets the RFC 1766 version of the input locale
     * @return {string} RFC 1766 version of the locale (i.e en-US)
     */
    public function getRFC1766Locale() {
        return i18n::convert_rfc1766($this->owner->Locale);
    }
}
?>