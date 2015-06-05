<?php
namespace GroupMeApiClient;

class EmojiUtilsTest extends \PHPUnit_Framework_TestCase {
    public function test_ShouldReturnEmojiSequenceByName() {
        $expected = array(1, 12);
        $actual = EmojiUtils::getEmojiSequenceByName(':cool_guy_face:');
        $this->assertEquals($expected, $actual);
    }
    
    public function test_ShouldReturnEmojiNameBySequence() {
        $expected = ':cigar_face:';
        $actual = EmojiUtils::getEmojiNameBySequence(array(1, 13));
        $this->assertEquals($expected, $actual);
    }
    
    public function test_ShouldReturnEmojiNameByPackAndIndex() {
        $expected = ':tweak_face:';
        $actual = EmojiUtils::getEmojiNameByPackAndIndex(1, 31);
        $this->assertEquals($expected, $actual);
    }
    
    public function test_ShouldReplaceEmojiNamesWithPlaceholders() {
        $ph = EmojiUtils::PLACEHOLDER;
        $text = 'Hello :smiley_face::smiley_face::pleased_face:';
        
        $expected = "Hello $ph$ph$ph";
        $result = EmojiUtils::extractEmojiNamesFromText($text);
        $this->assertEquals($expected, $result['text']);
    }
    
    public function test_ShouldReplacePlaceholdersWithEmojiNames() {
        $ph = EmojiUtils::PLACEHOLDER;
        $text = "Hello $ph$ph$ph";
        $charmap = array(array(1,0), array(1,0), array(1,2));
        
        $expected = "Hello :smiley_face::smiley_face::pleased_face:";
        $actual = EmojiUtils::injectEmojiNamesIntoText($text, $charmap);
        $this->assertEquals($expected, $actual);
    }
    
    public function test_ShouldReturnCharmap() {
        $text = 'Hello :smiley_face::smiley_face::pleased_face:';
        
        $expected = array(array(1,0), array(1,0), array(1,2));
        $result = EmojiUtils::extractEmojiNamesFromText($text);
        $this->assertEquals($expected, $result['charmap']);
    }
    
    public function test_ShouldNotMatchAnyEmojis() {
        $text = 'Hello :nonexistent_face:';
        
        $expected = "Hello :nonexistent_face:";
        $result = EmojiUtils::extractEmojiNamesFromText($text);
        $this->assertEquals($expected, $result['text']);
    }
}
