<?php

class ContentModerator {
    
    private $banned_words = [];
    private $db = null;
    
    public function __construct($db_connection = null) {
        $this->db = $db_connection;
        $this->loadBannedWords();
    }
    
    private function loadBannedWords() {
        $this->banned_words = [
            'terrorist' => ['bomb', 'explosive', 'terrorist', 'terrorism', 'jihad', 'isis', 'al-qaeda', 'massacre', 'beheading', 'assassination', 'suicide vest', 'grenade', 'rocket launcher', 'militia', 'extremist', 'radical'],
            'sexual' => ['porn', 'xxx', 'nude', 'naked', 'sex', 'erotic', 'slut', 'whore', 'fuck', 'shit', 'damn', 'ass', 'dick', 'cock', 'pussy', 'vagina', 'penis', 'boob', 'tits', 'strip', 'escort', 'prostitute', 'brothel', 'nsfw'],
            'cyberbullying' => ['kill', 'die', 'death', 'murder', 'hate', 'stupid', 'idiot', 'dumb', 'ugly', 'loser', 'pathetic', 'worthless', 'suicide', 'cut', 'selfharm', 'rope', 'hang', 'shoot', 'bully']
        ];
        
        if ($this->db) {
            $result = $this->db->query("SELECT word, category FROM moderation_words");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $cat = $row['category'];
                    $word = strtolower($row['word']);
                    if (!isset($this->banned_words[$cat])) {
                        $this->banned_words[$cat] = [];
                    }
                    if (!in_array($word, $this->banned_words[$cat])) {
                        $this->banned_words[$cat][] = $word;
                    }
                }
            }
        }
    }
    
    public function refreshWords() {
        $this->loadBannedWords();
    }
    
    public function moderateContent($content) {
        $result = [
            'status' => 'approved',
            'flagged_words' => [],
            'categories' => []
        ];
        
        $lower_content = strtolower($content);
        
        foreach ($this->banned_words as $category => $words_list) {
            foreach ($words_list as $word) {
                if (strpos($lower_content, $word) !== false) {
                    $result['flagged_words'][] = $word;
                    $result['categories'][] = $category;
                }
            }
        }
        
        $masked_patterns = [
            's[aeiouy*#@!]+x' => 'sex',
            'f[aeiou*#@!]+ck' => 'fuck',
            'sh[aeiou*#@!]+t' => 'shit',
            'k[aeiou*#@!]+ll' => 'kill',
            'p[aeiou*#@!]+rn' => 'porn',
            'n[aeiou*#@!]+ked' => 'naked',
            'sl[aeiou*#@!]+t' => 'slut',
            'wh[aeiou*#@!]+re' => 'whore',
            'd[aeiou*#@!]+ck' => 'dick',
            'm[aeiou*#@!]+rder' => 'murder',
            'b[aeiou*#@!]+mb' => 'bomb',
            'c[aeiou*#@!]+ck' => 'cock',
            'a[aeiou*#@!]+ss' => 'ass'
        ];
        
        foreach ($masked_patterns as $pattern => $word) {
            if (preg_match('/' . $pattern . '/i', $content)) {
                $result['flagged_words'][] = $word;
                $result['categories'][] = 'masked';
            }
        }
        
        $result['flagged_words'] = array_unique($result['flagged_words']);
        $result['categories'] = array_unique($result['categories']);
        
        if (!empty($result['flagged_words'])) {
            $result['status'] = 'pending';
        }
        
        return $result;
    }
}
