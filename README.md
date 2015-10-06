# GroupMe API Client
This library is an unofficial PHP wrapper for the [GroupMe v3 API](https://dev.groupme.com/).

### Installation
Install using Composer:
`"emilh91/groupme-api-client": "dev-master"`

### Boilerplate
Your GroupMe API key can be found on the page mentioned above once you are logged in.
```php
require 'vendor/autoload.php';
$c = new GroupMeApi\Client('API-KEY');
```

### Client methods
All the methods in the following sub-sections should be invoked on the newly created client object.

##### Bot methods
```php
public function getMyBots()
public function createBot($bot_name, $group_id, $avatar_url='', $callback_url='')
public function sendBotMessage($bot_id, $text, array $attachments=array())
public function destroyBot($bot_id)
```

##### Direct Message methods
```php
public function getDirectMessageChats($page=1, $per_page=10)
public function getLatestDirectMessages($other_user_id, $limit=20)
public function getDirectMessagesBefore($other_user_id, $message_id)
public function getDirectMessagesSince($other_user_id, $message_id)
public function sendDirectMessage($other_user_id, $text, $source_guid=null, array $attachments=array())
public function likeDirectMessage($other_user_id, $message_id)
public function unlikeDirectMessage($other_user_id, $message_id)
```

##### Group methods
```php
public function getAllGroups($page=1, $per_page=10)
public function getFormerGroups()
public function createGroup($name, $description='', $image_url='', $share=false)
public function getGroupDetails($group_id)
public function updateGroupDetails($group_id, array $payload)
public function destroyGroup($group_id)
public function joinGroup($group_id, $share_token)
public function rejoinGroup($group_id)
public function getLeaderboard($group_id, $period='day')
public function getLeaderboardForDay($group_id)
public function getLeaderboardForWeek($group_id)
public function getLeaderboardForMonth($group_id)
public function getMyLikes($group_id)
public function getMyHits($group_id)
public function addMembersToGroup($group_id, array $members)
public function getAddMembersToGroupResult($group_id, $results_id)
public function updateMyGroupMembership($group_id, $nickname)
public function removeGroupMember($group_id, $user_id)
public function getLatestGroupMessages($group_id, $limit=20)
public function getGroupMessagesBefore($group_id, $message_id, $limit=20)
public function getGroupMessagesAfter($group_id, $message_id, $limit=20)
public function getGroupMessagesSince($group_id, $message_id, $limit=20)
public function sendGroupMessage($group_id, $text, array $attachments=array(), $source_guid=null)
```

##### User methods
```php
public function getMyDetails()
public function updateMyDetails(array $payload)
public function enableSmsMode($duration, $registration_id)
public function disableSmsMode()
```

### Attachments
When sending messages (bot, direct, or group), you can specify an array of attachments. A factory class exists to easily create attachments: `GroupMeApi\AttachmentUtils`.
```php
public static function makeLocationAttachment($lat, $lng, $name='')
public static function makeImageAttachment($image_url)
public static function makeMentionsAttachment($users, $strpos)
public static function makeSplitAttachment()
public static function makeEmojiAttachment(array $charmap)
```

### Emojis
Aah, the pinnacle of modern communication... To send emojis in GroupMe, you need to specify a charmap (character map) when creating the attachment. For this purpose, another factory class exists: `GroupMeApi\EmojiUtils`.

```php
require 'vendor/autoload.php';
$c = new GroupMeApi\Client('API-KEY');

$raw_text = 'Hello :cool_guy_face::cigar_face:';
$emojification = GroupMeApi\EmojiUtils::extractEmojiNamesFromText($raw_text); // returns an array
$emoji_attachment = GroupMeApi\AttachmentUtils::makeEmojiAttachment($emojification['charmap']);
$c->sendDirectMessage('OTHER-USER-ID', $emojification['text'], null, array($emoji_attachment));
```

### Image Service
Before using images in messages you have to upload an image to GroupMe's image service.

```php
require 'vendor/autoload.php';
$c = new GroupMeApi\Client('API-KEY');
$res = $c->uploadImage('my_image_file.png', 'image/png', 'testpic');
```

If the upload was successfull, the return variable contains the image url in `$res['payload']['url']` or an error message in `$res['error'][]`.

Thanks to user [rgaida](https://github.com/rgaida) for fixing the image service!
