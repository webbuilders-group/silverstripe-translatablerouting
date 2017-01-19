<?php
class MultilingualSiteTreeExtension extends DataExtension {
    /**
     * Updates the CMS fields adding the fields defined in this extension
     * @param {FieldList} $fields Field List that new fields will be added to
     */
    public function updateCMSFields(FieldList $fields) {
        $urlSegmentField=$fields->dataFieldByName('URLSegment');
        if($urlSegmentField) {
            if(MultilingualRootURLController::config()->use_country_only) {
                $i18nSegment=strtolower(preg_replace('/^(.*?)_(.*?)$/', '$2', $this->owner->Locale));
            }else if(MultilingualRootURLController::config()->UseLocaleURL) {
                if(MultilingualRootURLController::config()->UseDashLocale) {
                    $i18nSegment=str_replace('_', '-', strtolower($this->owner->Locale));
                }else {
                    $i18nSegment=$this->owner->Locale;
                }
            }else {
                $i18nSegment=i18n::get_lang_from_locale($this->owner->Locale);
            }
            
            
            $baseLink=Controller::join_links(
                                            Director::absoluteBaseURL(),
                                            $i18nSegment.'/',
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