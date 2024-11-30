<?php

namespace WpAbSplit;

class Updater
{

    /**
     * URL to the mother ship
     */
    const MOTHERSHIP = 'http://cometex/wplicense/wp-content/plugins/socrates/updater/info.json';


    public static function plugin_info($res, $action, $args)
    {
        if('plugin_information' !== $action) {
            return $res;
        }

        if(WPAB_PLUGIN_SLUG !== $args->slug) {
            return $res;
        }

        $remote = wp_remote_get(
            self::MOTHERSHIP,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );

        if(is_wp_error($remote) || 200 !== wp_remote_retrieve_response_code($remote) || empty(wp_remote_retrieve_body($remote))) {
            return $res;
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->requires_php = $remote->requires_php;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->last_updated = $remote->last_updated;
        $res->sections = array(
            'description' => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog' => $remote->sections->changelog
        );

        $res->banners = array(
            'low' => $remote->banners->low,
            'high' => $remote->banners->high
        );

        return $res;
    }

    public static function plugin_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = wp_remote_get(
            self::MOTHERSHIP,
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );

        if(is_wp_error( $remote ) || 200 !== wp_remote_retrieve_response_code( $remote ) || empty( wp_remote_retrieve_body( $remote ))) {
            return $transient;
        }

        $remote = json_decode(wp_remote_retrieve_body($remote));

        if($remote && version_compare( WPAB_VERSION, $remote->version, '<' ) && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' ) && version_compare( $remote->requires_php, PHP_VERSION, '<' )) {
            $res = new \stdClass();
            $res->slug = $remote->slug;
            $res->plugin = WPAB_PLUGIN_SLUG . '/' . WPAB_PLUGIN_SLUG . '.php';
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $remote->download_url;
            $transient->response[ $res->plugin ] = $res;
        }

        return $transient;
    }

}