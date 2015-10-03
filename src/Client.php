<?php
namespace GroupMeApi;

class Client {
    private $token;

    public function __construct($token='') {
        $this->token = $token;
    }
    
    // Image Service methods
    public function uploadImage($image_file, $mime, $name) {
        $curl_file = new \CURLFile($image_file, $mime, $name);
        $payload = array('file' => $curl_file);
        return $this->request('POST', '/pictures', array(), $payload, true);
    }
    
    // Bot methods
    public function getMyBots() {
        return $this->get('/bots');
    }
    
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
    
    public function sendBotMessage($bot_id, $text, array $attachments=array()) {
        $payload = array(
            'bot_id' => $bot_id,
            'text' => $text,
            'attachments' => $attachments
        );
        return $this->post('/bots/post', $payload);
    }

    public function destroyBot($bot_id) {
        $payload = array('bot_id' => $bot_id);
        return $this->post('/bots/destroy', $payload);
    }
    
    // Direct Message methods
    public function getOtherUserIdFromConversationId($conversation_id) {
        $my_details = $this->getMyDetails();
        $my_user_id = $my_details['response']['id'];
        $user_ids = explode('+', $conversation_id);
        return $my_user_id==$user_ids[0] ? $user_ids[1] : $user_ids[0];
    }
    
    public function getConversationIdFromOtherUserId($other_user_id) {
        $my_details = $this->getMyDetails();
        $my_user_id = intval($my_details['response']['id']);
        $o_user_id = intval($other_user_id);
        return min($my_user_id,$o_user_id) . '+' . max($my_user_id,$o_user_id);
    }
    
    public function getDirectMessageChats($page=1, $per_page=10) {
        $query = array(
            'page' => $page,
            'per_page' => $per_page
        );
        return $this->get('/chats', $query);
    }
    
    public function getLatestDirectMessages($other_user_id, $limit=20) {
        $query = array(
            'other_user_id' => $other_user_id,
            'limit' => $limit
        );
        return $this->get('/direct_messages', $query);
    }
    
    public function getDirectMessagesBefore($other_user_id, $message_id) {
        $query = array(
            'other_user_id' => $other_user_id,
            'before_id' => $message_id
        );
        return $this->get('/direct_messages', $query);
    }
    
    public function getDirectMessagesSince($other_user_id, $message_id) {
        $query = array(
            'other_user_id' => $other_user_id,
            'since_id' => $message_id
        );
        return $this->get('/direct_messages', $query);
    }

    public function sendDirectMessage($other_user_id, $text, $source_guid=null, array $attachments=array()) {
        $message_info = array(
            'recipient_id' => $other_user_id,
            'text' => $text,
            'source_guid' => $source_guid ?: "D$other_user_id-". date('YmdHis-') . uniqid(),
            'attachments' => $attachments
        );
        $payload = array('direct_message' => $message_info);
        return $this->post('/direct_messages', $payload);
    }
    
    public function likeDirectMessage($other_user_id, $message_id) {
        $conversation_id = $this->getConversationIdFromOtherUserId($other_user_id);
        return $this->post("/messages/$conversation_id/$message_id/like");
    }
    
    public function unlikeDirectMessage($other_user_id, $message_id) {
        $conversation_id = $this->getConversationIdFromOtherUserId($other_user_id);
        return $this->post("/messages/$conversation_id/$message_id/unlike");
    }
    
    // Group methods
    public function getAllGroups($page=1, $per_page=10) {
        $query = array(
            'page' => $page,
            'per_page' => $per_page
        );
        return $this->get('/groups', $query);
    }
    
    public function getFormerGroups() {
        return $this->get('/groups/former');
    }
    
    public function createGroup($name, $description='', $image_url='', $share=false) {
        $payload = array(
            'name' =>$name,
            'description' => $description,
            'image_url' => $image_url,
            'share' => boolval($share)
        );
        return $this->post('/groups', $payload);
    }
    
    public function getGroupDetails($group_id) {
        return $this->get("/groups/$group_id");
    }
    
    public function updateGroupDetails($group_id, array $payload) {
        return $this->post("/groups/$group_id/update", $payload);
    }
    
    public function destroyGroup($group_id) {
        return $this->post("/groups/$group_id/destroy");
    }

    public function joinGroup($group_id, $share_token) {
        return $this->post("/groups/$group_id/join/$share_token");
    }

    public function rejoinGroup($group_id) {
        $payload = array('group_id' => $group_id);
        return $this->post('/groups/join', $payload);
    }
    
    public function getLeaderboard($group_id, $period='day') {
        $query = array('period' => $period);
        return $this->get("/groups/$group_id/likes", $query);
    }
    
    public function getLeaderboardForDay($group_id) {
        return $this->getLeaderboard($group_id, 'day');
    }
    
    public function getLeaderboardForWeek($group_id) {
        return $this->getLeaderboard($group_id, 'week');
    }
    
    public function getLeaderboardForMonth($group_id) {
        return $this->getLeaderboard($group_id, 'month');
    }

    public function getMyLikes($group_id) {
        return $this->get("/groups/$group_id/likes/mine");
    }

    public function getMyHits($group_id) {
        return $this->get("/groups/$group_id/likes/for_me");
    }
    
    public function addMembersToGroup($group_id, array $members) {
        $payload = array('members' => $members);
        return $this->post("/groups/$group_id/members/add", $payload);
    }

    public function updateMyGroupMembership($group_id, $nickname) {
        $membership_info = array('nickname' => $nickname);
        $payload = array('membership' => $membership_info);
        return $this->post("/groups/$group_id/memberships/update", $payload);
    }

    public function getGroupMembers($group_id, $results_id) {
        return $this->get("/groups/$group_id/members/results/$results_id");
    }
    
    public function removeGroupMember($group_id, $user_id) {
        return $this->post("/groups/$group_id/members/$user_id/remove");
    }
    
    public function getLatestGroupMessages($group_id, $limit=20) {
        $query = array('limit' => $limit);
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    public function getGroupMessagesBefore($group_id, $message_id, $limit=20) {
        $query = array(
            'before_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    public function getGroupMessagesAfter($group_id, $message_id, $limit=20) {
        $query = array(
            'after_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }
    
    public function getGroupMessagesSince($group_id, $message_id, $limit=20) {
        $query = array(
            'since_id' => $message_id,
            'limit' => $limit
        );
        return $this->get("/groups/$group_id/messages", $query);
    }

    public function sendGroupMessage($group_id, $text, $source_guid=null, array $attachments=array()) {
        $message_info = array(
            'text' => $text,
            'source_guid' => $source_guid ?: "G$group_id-" . date('YmdHis-') . uniqid(),
            'attachments' => $attachments
        );
        $payload = array('message' => $message_info);
        return $this->post("/groups/$group_id/messages", $payload);
    }
    
    // User methods
    public function getMyDetails() {
        return $this->get('/users/me');
    }
    
    public function updateMyDetails(array $payload) {
        return $this->post('/users/update', $payload);
    }
    
    public function enableSmsMode($duration, $registration_id) {
        $payload = array(
            'duration' => $duration,
            'registration_id' => $registration_id
        );
        return $this->post('/users/sms_mode', $payload);
    }

    public function disableSmsMode() {
        return $this->post('/users/sms_mode/delete');
    }
    
    // Core methods
    private function get($endpoint, array $query=array()) {
        return $this->request('GET', $endpoint, $query);
    }
    
    private function post($endpoint, array $payload=array()) {
        return $this->request('POST', $endpoint, array(), $payload);
    }
    
    private function request($method, $endpoint, array $query=array(), array $payload=array(), $img_svc_url=false) {
        if ($img_svc_url) {
            $base_url = 'https://image.groupme.com';
            $header = 'Content-Type: multipart/form-data';
        }
        else {
            $base_url = 'https://api.groupme.com/v3';
            $header = 'Content-Type: application/json';
        }

        $query['access_token'] = $this->token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_URL, $base_url . $endpoint . '?' . http_build_query($query));
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
