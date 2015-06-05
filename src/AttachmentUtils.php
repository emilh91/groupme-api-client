<?php
namespace GroupMeApiClient;

class AttachmentUtils {
    private function __construct() {}
    
    public static function makeLocationAttachment($lat, $lng, $name='') {
        return array(
            'type' => 'location',
            'lat' => "$lat",
            'lng' => "$lng",
            'name' => "$name",
        );
    }
    
    public static function makeImageAttachment($image_url) {
        return array(
            'type' => 'image',
            'url' => $image_url,
        );
    }
    
    public static function makeSplitAttachment() {
        return array(
            'type' => 'split',
            'token' => 'SPLIT_TOKEN',
        );
    }
    
    public static function makeEmojiAttachment(array $charmap) {
        return array(
            'type' => 'emoji',
            'placeholder' => EmojiUtils::PLACEHOLDER,
            'charmap' => $charmap,
        );
    }
}
