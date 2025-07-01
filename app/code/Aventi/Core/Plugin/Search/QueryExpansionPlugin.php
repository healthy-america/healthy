<?php

namespace Aventi\Core\Plugin\Search;

use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;

class QueryExpansionPlugin
{
    public function beforeCreate(
        QueryFactory $subject,
        $requestName,
         $queryText,
        array $queryParams = []
    ) {
            if(isset($queryText['queryText'])){
                $text =  $queryText['queryText'];
                $addText = $this->getLastLetterIfEndsWithLetter($text);
                if(is_string($text) && trim($text) !== ''){
                    $queryText['queryText'] = $addText;
                }
            }

        return [$requestName, $queryText, $queryParams];
    }

        /**
         * Normalize the search query:
         * 1. If a word contains digits followed by letters insert a space between them ("480MG", "D3").
         * 2. If the query has exactly two words and the second one is a single letter (e.g., "vitamina c"),
         *  repeat the letter to improve search matching (e.g., "vitamina c").
         *
         * @param string $text The original search query.
         * @return string The normalized search query.
         */
        private function getLastLetterIfEndsWithLetter(string $text): string
    {
        $text = trim($text);

        // Step 1: Insert space between digits and letters in both directions
        $text = preg_replace('/(?<=[a-zA-Z])(?=\d)/u', ' ', $text); // Letter followed by digit
        $text = preg_replace('/(?<=\d)(?=[a-zA-Z])/u', ' ', $text); // Digit followed by letter

        // Step 2: If query has exactly two words and the second one is a single letter, repeat it
        $words = preg_split('/\s+/', $text);
        if (count($words) === 2) {
            $lastWord = $words[1];

            // Only repeat if the second word is a single Unicode letter
            if (mb_strlen($lastWord) === 1 && preg_match('/^\p{L}$/u', $lastWord)) {
                if (!preg_match('/\b' . preg_quote($lastWord, '/') . '\b\s+\b' . preg_quote($lastWord, '/') . '\b$/iu', $text)) {
                    $text .= ' ' . $lastWord;
                }
            }
        }

        return $text;
    }
}
