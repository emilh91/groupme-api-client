<?php
namespace GroupMeApi;

class AttachmentUtils {
    private function __construct() {}
    
    /**
     * GroupMe Location Attachment
     * 
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param string $name Location description or name
     * 
     * @return string[]
     */
    public static function makeLocationAttachment($lat, $lng, $name='') {
        return array(
            'type' => 'location',
            'lat' => "$lat",
            'lng' => "$lng",
            'name' => "$name",
        );
    }
    
    /**
     * GroupMe Mentions Attachment
     * 
     * $strpos needs a tuple for each user id with the first item consisting of 
     * the index of the start of the mention, and the second item is the length 
     * of the mention text
     * 
     * @param int[] $users Array of user ids to mention
     * @param int[] $strpos Text positions of user names to highlight
     * 
     * @return array
     */
    public static function makeMentionsAttachment($users, $strpos) {
        return array(
            'type' => 'mentions',
            'user_ids' => $users,
            'loci' => $strpos
        );
    }

    /**
     * Gets text position tuples for the mentions attachment
     * 
     * @param mixed $txt       Message text including user mention(s)
     * @param mixed $usernames User name(s) of mentioned user(s)
     * @param mixed $mchar     Mention indicator (typically "@")
     * 
     * @return array[] Position and length of user names in message
     */
    public static function getUsernamePositions($txt, $usernames, $mchar = '@') {
        $loci = array();

        foreach ($usernames as $username) {
            $mention = $mchar . $username;
            $pos = strpos($txt, $mention);

            if ($pos === FALSE) break;

            $loci[] = array($pos, strlen($mention));
        }

        return $loci;
    }

    /**
     * GroupMe Image Attachment
     * 
     * @param string $image_url GroupMe Image Service URL
     * 
     * @return array
     */
    public static function makeImageAttachment($image_url) {
        return array(
            'type' => 'image',
            'url' => $image_url,
        );
    }
    
    /**
     * Summary of makeSplitAttachment
     * 
     * @return string[]
     */
    public static function makeSplitAttachment() {
        return array(
            'type' => 'split',
            'token' => 'SPLIT_TOKEN',
        );
    }
    
    /**
     * GroupMe Emoji Attachment
     * 
     * @param array $charmap 
     * 
     * @return array
     */
    public static function makeEmojiAttachment(array $charmap) {
        return array(
            'type' => 'emoji',
            'placeholder' => EmojiUtils::PLACEHOLDER,
            'charmap' => $charmap,
        );
    }
}
