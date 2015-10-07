<?php

namespace GroupMeApi;

class Client {
    private $token;

    /**
     * Class constructor
     * 
     * @param string $token GroupMe API Token
     */
    public function __construct($token='') {
        $this->token = $token;
    }
    
    // IMAGE SERVICE METHODS

    /**
     * Uploads an image to the GroupMe Image Service
     * 
     * @param string $image_file Filename fo the image file
     * @param string $mime Mime type of the image file (e.g. 'image/png')
     * @param string $name Optional image name or description
     * 
     * @return string[] API result
     */
    public function uploadImage($image_file, $mime, $name = '') {
        $curl_file = new \CURLFile($image_file, $mime, $name);
        $payload = array('file' => $curl_file);
        return $this->request('POST', '/pictures', array(), $payload, true);
    }
    
    // BOT METHODS

    /**
     * Lists your existing bots
     * 
     * @return string[] API result
     */
    public function getMyBots() {
        return $this->get('/bots');
    }
    
    /**
     * Creates a new bot
     * 
     * @param string $bot_name     Name of the bot
     * @param int    $group_id     Group id where the bot will be used 
     * @param string $avatar_url   Avatar image (GroupMe ImgService url)
     * @param string $callback_url Callback url
     * 
     * @return string[] API result with bot id on success
     */
    public function createBot($bot_name, $group_id, $avatar_url='', $callback_url='') {  
        $bot_info = array(
            'name' => $bot_name,
            'group_id' => $group_id,
            'avatar_url' => $avatar_url,
            'callback_url' => $callback_url
        );
        $payload = array('bot' => $bot_info);
        return $this->post('/bots', $payload);
    }
    
    /**
     * Sends a bot message
     * 
     * @param string $bot_id      Bot id
     * @param string $text        Message to send
     * @param array  $attachments Message attachments
     * 
     * @return string[] API result
     */
    public function sendBotMessage($bot_id, $text, array $attachments=array()) {
        $payload = array(
            'bot_id' => $bot_id,
            'text' => $text,
            'attachments' => $attachments
        );
        return $this->post('/bots/post', $payload);
    }

    /**
     * Destroys a bot
     * 
     * @param string $bot_id Bot id
     * 
     * @return string[] API result
     */
    public function destroyBot($bot_id) {
        $payload = array('bot_id' => $bot_id);
        return $this->post('/bots/destroy', $payload);
    }
    
    // DIRECT MESSAGE METHODS

    /**
     * Summary of getOtherUserIdFromConversationId
     * @param mixed $conversation_id 
     * @return mixed
     */
    public function getOtherUserIdFromConversationId($conversation_id) {
        $my_details = $this->getMyDetails();
        $my_user_id = $my_details['response']['id'];
        $user_ids = explode('+', $conversation_id);
        return $my_user_id==$user_ids[0] ? $user_ids[1] : $user_ids[0];
    }

    /**
     * Summary of getConversationIdFromOtherUserId
     * @param mixed $other_user_id 
     * @return string
     */
    public function getConversationIdFromOtherUserId($other_user_id) {
        $my_details = $this->getMyDetails();
        $my_user_id = intval($my_details['response']['id']);
        $o_user_id = intval($other_user_id);
        return min($my_user_id,$o_user_id) . '+' . max($my_user_id,$o_user_id);
    }
    
    /**
     * Gets direct message chats
     * 
     * Returns a paginated list of direct message chats, or conversations, 
     * sorted by updated_at descending
     * 
     * @param int $page     Page number
     * @param int $per_page Number of chats per page
     * 
     * @return mixed
     */
    public function getDirectMessageChats($page=1, $per_page=10) {
        $query = array(
            'page' => $page,
            'per_page' => $per_page
        );
        return $this->get('/chats', $query);
    }
    
    /**
     * Fetches direct messages between two users
     * 
     * @param string $other_user_id The other participant
     * @param int    $limit         Number of messages to retrieve
     * 
     * @return mixed
     */
    public function getLatestDirectMessages($other_user_id, $limit=20) {
        $query = array(
            'other_user_id' => $other_user_id,
            'limit' => $limit
        );
        return $this->get('/direct_messages', $query);
    }
    
    /**
     * Fetches 20 messages created before the given message ID
     * 
     * @param string $other_user_id The other participant
     * @param string $message_id    Message id
     * 
     * @return mixed
     */
    public function getDirectMessagesBefore($other_user_id, $message_id) {
        $query = array(
            'other_user_id' => $other_user_id,
            'before_id' => $message_id
        );
        return $this->get('/direct_messages', $query);
    }
    
    /**
     * Fetches 20 messages created after the given message ID
     * 
     * @param string $other_user_id The other participant
     * @param string $message_id    Message id
     * 
     * @return mixed
     */
    public function getDirectMessagesSince($other_user_id, $message_id) {
        $query = array(
            'other_user_id' => $other_user_id,
            'since_id' => $message_id
        );
        return $this->get('/direct_messages', $query);
    }

    /**
     * Sends a DM to another user
     * 
     * @param string $other_user_id The other participant
     * @param string $text          Message text
     * @param array  $attachments   Message attachments
     * @param string $source_guid   Unique id
     * 
     * @return mixed
     */
    public function sendDirectMessage($other_user_id, $text, array $attachments=array(),
        $source_guid=null) {

        $message_info = array(
            'recipient_id' => $other_user_id,
            'text' => $text,
            'source_guid' => $source_guid ?: "D$other_user_id-". 
                date('YmdHis-') . uniqid(),
            'attachments' => $attachments
        );
        $payload = array('direct_message' => $message_info);
        return $this->post('/direct_messages', $payload);
    }
    
    /**
     * Likes a message
     * 
     * @param string $other_user_id The other participant
     * @param string $message_id    Message to like
     * 
     * @return mixed
     */
    public function likeDirectMessage($other_user_id, $message_id) {
        $conversation_id = $this->getConversationIdFromOtherUserId($other_user_id);
        return $this->post("/messages/$conversation_id/$message_id/like");
    }
    
    /**
     * Unlikes a message
     * 
     * @param string $other_user_id The other participant
     * @param string $message_id    Message to like
     * 
     * @return mixed
     */
    public function unlikeDirectMessage($other_user_id, $message_id) {
        $conversation_id = $this->getConversationIdFromOtherUserId($other_user_id);
        return $this->post("/messages/$conversation_id/$message_id/unlike");
    }
    
    // GROUP METHODS

    /**
     * Lists the authenticated user's active groups
     * 
     * @param int $page     Fetch a particular page of results (defaults to 1)
     * @param int $per_page Messages per page (defaults to 10)
     * 
     * @return mixed
     */
    public function getAllGroups($page=1, $per_page=10) {
        $query = array(
            'page' => $page,
            'per_page' => $per_page
        );
        return $this->get('/groups', $query);
    }
    
    /**
     * Lists the groups you have left but can rejoin
     * 
     * @return mixed
     */
    public function getFormerGroups() {
        return $this->get('/groups/former');
    }
    
    /**
     * Creates a new group
     * 
     * @param string $name        Primary name of the group (max 140 characters)
     * @param string $description A subheading for the group (max 255 characters)
     * @param string $image_url   GroupMe Image Service URL
     * @param bool   $share       If true, a share URL will be created
     * 
     * @return mixed
     */
    public function createGroup($name, $description='', $image_url='', $share=false) {
        $payload = array(
            'name' => substr($name, 0, (strlen($name) <= 140) ? strlen($name) : 140),
            'description' => substr($description, 0, 
                (strlen($description) <= 255) ? strlen($description) : 255),
            'image_url' => $image_url,
            'share' => boolval($share)
        );
        return $this->post('/groups', $payload);
    }
    
    /**
     * Retrieves a specific group
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getGroupDetails($group_id) {
        return $this->get("/groups/$group_id");
    }
    
    /**
     * Updates a group after creation
     * 
     * $payload = array(
     *     'name'        => ...,
     *     'share'       => ...,
     *     'image_url'   => ...,
     *     'office_mode' => ...
     * );
     *
     * @param mixed $group_id 
     * @param array $payload 
     * @return mixed
     */
    public function updateGroupDetails($group_id, array $payload) {
        
        return $this->post("/groups/$group_id/update", $payload);
    }
    
    /**
     * Destroys a group
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function destroyGroup($group_id) {
        return $this->post("/groups/$group_id/destroy");
    }

    /**
     * Joins a shared group
     * 
     * @param string $group_id    Group id
     * @param string $share_token Share token
     * 
     * @return mixed
     */
    public function joinGroup($group_id, $share_token) {
        return $this->post("/groups/$group_id/join/$share_token");
    }

    /**
     * Rejoins a group. 
     * 
     * Only works if you previously removed yourself
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function rejoinGroup($group_id) {
        $payload = array('group_id' => $group_id);
        return $this->post('/groups/join', $payload);
    }
    
    /**
     * Gets a list of liked messages for a given period of time
     * 
     * Messages are ranked in order of number of likes.
     * 
     * @param string $group_id Group id
     * @param string $period Period of: 'day', 'week', or 'month' 
     * 
     * @return mixed
     */
    public function getLeaderboard($group_id, $period='day') {
        $query = array('period' => $period);
        return $this->get("/groups/$group_id/likes", $query);
    }
    
    /**
     * Gets a list of liked messages for a day
     * 
     * Messages are ranked in order of number of likes.
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getLeaderboardForDay($group_id) {
        return $this->getLeaderboard($group_id, 'day');
    }
    
    /**
     * Gets a list of liked messages for a week
     * 
     * Messages are ranked in order of number of likes.
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getLeaderboardForWeek($group_id) {
        return $this->getLeaderboard($group_id, 'week');
    }
    
    /**
     * Gets a list of liked messages for a month
     * 
     * Messages are ranked in order of number of likes.
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getLeaderboardForMonth($group_id) {
        return $this->getLeaderboard($group_id, 'month');
    }

    /**
     * Fetches a list of messages you have liked
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getMyLikes($group_id) {
        return $this->get("/groups/$group_id/likes/mine");
    }

    /**
     * Fetches a list of messages others have liked
     * 
     * @param string $group_id Group id
     * 
     * @return mixed
     */
    public function getMyHits($group_id) {
        return $this->get("/groups/$group_id/likes/for_me");
    }
    
    /**
     * Adds members to a group
     * 
     * To add a member, you must use one of the following 
     * identifiers: user_id, phone_number, or email. 
     * 
     * $new_member = array(
     *     string $nickname, // required
     *     string $user_id,
     *     string $phone_number,
     *     string $email,
     *     string $guid
     * );
     * 
     * @param string $group_id Group id
     * @param array  $members  One or more members to add
     * 
     * @return mixed
     */
    public function addMembersToGroup($group_id, array $members) {
        $payload = array('members' => $members);
        return $this->post("/groups/$group_id/members/add", $payload);
    }

    /**
     * Gets the membership results from an add call
     * 
     * @param string $group_id Group id
     * @param string $guid     The guid that's returned from an add request. 
     * 
     * @return mixed
     */
    public function getAddMembersToGroupResult($group_id, $guid) {
        return $this->get("/groups/$group_id/members/results/$guid");
    }

    /**
     * Updates your nickname in a group
     * 
     * The nickname must be between 1 and 50 characters
     * 
     * @param string $group_id Group id
     * @param string $nickname Nickname
     * 
     * @return mixed
     */
    public function updateMyGroupMembership($group_id, $nickname) {
        $maxNickLen = 50;
        $nickname = (strlen($nickname) <= $maxNickLen) ? $nickname : substr($nickname, 0, $maxNickLen - 1);
        $membership_info = array('nickname' => $nickname);
        $payload = array('membership' => $membership_info);
        return $this->post("/groups/$group_id/memberships/update", $payload);
    }
    
    /**
     * Removes a member (or yourself) from a group
     * 
     * @param string $group_id Group id
     * @param string $user_id  User id
     * 
     * @return mixed
     */
    public function removeGroupMember($group_id, $user_id) {
        return $this->post("/groups/$group_id/members/$user_id/remove");
    }
    
    /**
     * Retrieves messages for a group
     * 
     * By default, messages are returned in groups of 20, ordered by 
     * created_at descending. This can be raised or lowered by passing 
     * a limit parameter, up to a maximum of 100 messages.
     *
     * @param int $group_id Group id
     * @param int $limit    Number of messages to retrieve
     * 
     * @return mixed
     */
    public function getLatestGroupMessages($group_id, $limit=20) {
        $query = array('limit' => $limit);
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    /**
     * Retrieves messages created before the given message ID
     * 
     * @param int    $group_id   Group id
     * @param string $message_id Message id
     * @param int    $limit      Number of messages to retrieve
     * 
     * @return mixed
     */
    public function getGroupMessagesBefore($group_id, $message_id, $limit=20) {
        $query = array(
            'before_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    /**
     * Retrieves messages created immediately after the given message ID
     * 
     * @param int    $group_id   Group id
     * @param string $message_id Message id
     * @param int    $limit      Number of messages to retrieve
     * 
     * @return mixed
     */
    public function getGroupMessagesAfter($group_id, $message_id, $limit=20) {
        $query = array(
            'after_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    /**
     * Retrieves most recent messages created after the given message ID 
     * 
     * @param int    $group_id   Group id
     * @param string $message_id Message id
     * @param int    $limit      Number of messages to retrieve
     * 
     * @return mixed
     */
    public function getGroupMessagesSince($group_id, $message_id, $limit=20) {
        $query = array(
            'since_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }

    /**
     * Sends a message to a group
     * 
     * @param int    $group_id    Group id
     * @param string $text        Message text
     * @param string $source_guid Unique id
     * @param array  $attachments Message attachments
     * 
     * @return mixed
     */
    public function sendGroupMessage($group_id, $text, $source_guid=null, 
        array $attachments=array()) {

        $message_info = array(
            'text' => $text,
            'source_guid' => $source_guid ?: "G$group_id-" . date('YmdHis-') . uniqid(),
            'attachments' => $attachments
        );
        $payload = array('message' => $message_info);
        return $this->post("/groups/$group_id/messages", $payload);
    }
    
    // USER METHODS

    /**
     * Gets details about the authenticated user
     * 
     * @return mixed
     */
    public function getMyDetails() {
        return $this->get('/users/me');
    }
    
    /**
     * Updates attributes about your own account
     * 
     * $payload = array(
     *     string $avatar_url URL to valid JPG/PNG/GIF image,
     *     string $name       Name must be of the form FirstName LastName,
     *     string $email      Email address. Must be in name@domain.com form,
     *     string $zip        Zip code
     * )
     * 
     * @return mixed
     */
    public function updateMyDetails($payload) {
        return $this->post('/users/update', $payload);
    }

    /**
     * Enables SMS mode 
     * 
     * Enables SMS mode for N hours, where N is at most 48. 
     * After N hours have elapsed, user will receive push notfications
     *
     * If the push notification ID/token that should be suppressed during 
     * SMS mode is omitted, both SMS and push notifications 
     * will be delivered to the device. 
     * 
     * @param mixed $duration N hour duration
     * @param string $registration_id Push notification ID/token
     * 
     * @return mixed 
     */
    public function enableSmsMode($duration, $registration_id) {
        $payload = array(
            'duration' => $duration,
            'registration_id' => $registration_id
        );
        return $this->post('/users/sms_mode', $payload);
    }

    /**
     * DISABLES SMS MODE
     * 
     * @return mixed
     */
    public function disableSmsMode() {
        return $this->post('/users/sms_mode/delete');
    }
    
    // Core methods

    /**
     * Gets data from an endpoint
     * 
     * @param string $endpoint API endpoint
     * @param array  $query    Request
     * 
     * @return mixed
     */
    private function get($endpoint, array $query=array()) {
        return $this->request('GET', $endpoint, $query);
    }
    
    /**
     * Posts data to an endpoint
     * 
     * @param string $endpoint API endpoint
     * @param array $payload   Payload
     * 
     * @return mixed
     */
    private function post($endpoint, array $payload=array()) {
        return $this->request('POST', $endpoint, array(), $payload);
    }
    
    /**
     * Sends a curl post/get request
     * 
     * @param string $method      POST or GET method
     * @param string $endpoint    Endpoint path
     * @param array  $query       Query
     * @param array  $payload     Payload
     * @param bool   $img_svc_url Image upload?
     * 
     * @return mixed API result
     */
    private function request($method, $endpoint, array $query=array(), 
        array $payload=array(), $img_svc_url=false) {

        if ($img_svc_url) {
            $base_url = 'https://image.groupme.com';
            $header = 'Content-Type: multipart/form-data';
        }
        else {
            $base_url = 'https://api.groupme.com/v3';
            $header = 'Content-Type: application/json';
            $payload = json_encode($payload);
        }

        $query['access_token'] = $this->token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));

        curl_setopt($ch, CURLOPT_URL, 
            $base_url . $endpoint . '?' . http_build_query($query));

        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'GroupMe API Client');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($method == 'POST') {
            $data = $img_svc_url ? $payload : json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
