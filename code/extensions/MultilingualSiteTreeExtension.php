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
                                            (Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? $this->owner->Locale:i18n::get_lang_from_locale($this->owner->Locale)).'/',
                                            (SiteTree::config()->nested_urls && $this->owner->ParentID ? $this->owner->Parent()->RelativeLink(true):null)
                                        );
            
            
            $urlSegmentField->setURLPrefix($baseLink);
        }
    }
}
?>