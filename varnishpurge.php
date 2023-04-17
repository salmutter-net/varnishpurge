<?php
defined( '_JEXEC' ) or die;
jimport( 'joomla.plugin.plugin' );

class plgSystemVarnishPurge extends JPlugin {

    public function onContentAfterSave( $context, $item, $isNew ) {
        if ( $context === 'com_content.article' ) {
            if ( !$isNew ) {
                $this->purgeCache( rtrim( JURI::root(), '/') . \Joomla\CMS\Router\Route::link( 'site', ContentHelperRoute::getArticleRoute( $item->id, $item->catid ) ) );
                $this->purgeCache( rtrim( JURI::root(), '/') . \Joomla\CMS\Router\Route::link( 'site', ContentHelperRoute::getCategoryRoute( $item->catid ) ) );
            }
        }
        if ( $context === 'com_categories.category' ) {
            if ( !$isNew ) {
                $this->purgeCache( rtrim( JURI::root(), '/') . \Joomla\CMS\Router\Route::link( 'site', ContentHelperRoute::getCategoryRoute( $item->id ) ) );
            }
        }
        if ( $context === 'com_contact.contact' ) {
            if ( !$isNew ) {
                $this->purgeCache( rtrim( JURI::root(), '/') . \Joomla\CMS\Router\Route::link( 'site', \Joomla\Component\Contact\Site\Helper\RouteHelper::getContactRoute($item->slug, $item->catslug, $item->language) ) );
            }
        }
        if ( $context === 'com_menus.item' ) {
            if ( !$isNew ) {
                $this->purgeCache( rtrim( JURI::root(), '/' ) . \Joomla\CMS\Router\Route::link( 'site', 'index.php?Itemid='.$item->id) );
            }
        }
    }

    protected function purgeCache( $url ) {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_USERAGENT,'joomla_purgeCache');
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PURGE' );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_URL, $url );
        $result = curl_exec( $curl );
        curl_close( $curl );

        JFactory::getApplication()->enqueueMessage('⚡ Varnish cache: URL [ "' . $url . '" ] wurde gepurged.');

        $this->cacheUrl($url);

        return true;
    }

    private function cacheUrl( $url ) {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_USERAGENT,'joomla_warmCache');
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_URL, $url );
        $result = curl_exec( $curl );
        curl_close( $curl );

        return true;
    }
}
