<?php
defined( '_JEXEC' ) or die;
jimport( 'joomla.plugin.plugin' );

class plgSystemVarnishPurge extends JPlugin {
    public function onContentAfterSave( $context, $item, $isNew ) {
        if ( $context === 'com_content.article' ) {
            $this->purgeCache( \Joomla\CMS\Router\Route::_( ContentHelperRoute::getArticleRoute( $item->id, $item->catid ) ) );
            $this->purgeCache( \Joomla\CMS\Router\Route::_( ContentHelperRoute::getCategoryRoute( $item->catid ) ) );
        }
        if ( $context === 'com_categories.category' ) {
            $this->purgeCache( \Joomla\CMS\Router\Route::_( ContentHelperRoute::getCategoryRoute( $item->id ) ) );
        }
    }

    protected function purgeCache( $url ) {
        $host = $this->params->get( 'varnish_host', '127.0.0.1' );
        $port = $this->params->get( 'varnish_port', '80' );
        $urlFormatted = $this->getUrl($url);
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_USERAGENT,'joomla_purgeCache');
        curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PURGE' );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, ['Host: ' . $urlFormatted['hostname']]);
        curl_setopt( $curl, CURLOPT_URL, $urlFormatted[ 'url' ] );
        $result = curl_exec( $curl );
        curl_close( $curl );

        JFactory::getApplication()->enqueueMessage('⚡ Varnish cache: URL [ "' . $urlFormatted[ 'url' ] . '" ] wurde gepurged.');

        return true;
    }

    private function getUrl( $url ) {
        $parsedUrl = parse_url( $url );
        $hostname = $parsedUrl[ 'host' ];
        $address = gethostbyname( $hostname );
        $url = $parsedUrl[ 'scheme' ] . '://' . $address;
        if ( $parsedUrl[ 'port' ] ) {
            $url .= ':' . $parsedUrl[ 'port' ];
        }
        if ( $parsedUrl[ 'path' ] ) {
            $url .= $parsedUrl[ 'path' ];
        }
        if ( $parsedUrl[ 'query' ] ) {
            $url .= '?' . $parsedUrl[ 'query' ];
        }
        if ( $parsedUrl[ 'fragment' ] ) {
            $url .= '#' . $parsedUrl[ 'fragment' ];
        }
        return [
            'url' => $url,
            'hostname' => $hostname
        ];
    }
}
