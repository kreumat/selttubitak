<?php
/**
 * Random Prompts for Board Game Generator
 * 
 * This file contains an array of random sentences that can be used
 * to automatically populate board game tiles when the user clicks
 * the dice icon. These prompts are designed to be engaging and varied
 * to inspire creativity and fun gameplay.
 */

// Array of random prompts for board game tiles
$random_prompts = [
    "Tell a funny joke to the group.",
    "Name 3 things that make you happy.",
    "Imitate your favorite animal.",
    "What superpower would you like to have?",
    "If you could meet any person, who would it be?",
    "Tell us about your favorite movie.",
    "Make up a new rule for this game.",
    "Go back 2 spaces.",
    "Move forward 3 spaces.",
    "Choose another player to skip their turn.",
    "Everyone move 1 space forward.",
    "Swap places with another player.",
    "You found a shortcut! Move to any space on the board.",
    "Describe your perfect day.",
    "Name 3 countries you'd like to visit.",
    "If you were an animal, what would you be?",
];

/**
 * Function to get a random prompt
 * 
 * @return string A random prompt from the array
 */
function getRandomPrompt() {
    global $random_prompts;
    return $random_prompts[array_rand($random_prompts)];
}

/**
 * Function to get all prompts as JSON
 * 
 * @return string JSON encoded array of all prompts
 */
function getAllPromptsJson() {
    global $random_prompts;
    return json_encode($random_prompts);
}

/**

    "Sing the chorus of your favorite song.",
    "Skip your next turn.",
	"What would you do with a million dollars?",
    "Answer a question from another player.",
    "Spell your name backwards.",
    "Share an interesting fact about yourself.",
    "Tell us your favorite childhood memory.",
    "Do 5 jumping jacks.",
    "Count backwards from 20 to 10.",
    "What's your favorite food and why?",
    "Describe your dream vacation in 3 words.",
    "What's your favorite board game?",
    "Draw a quick animal on a piece of paper.",
*/
?>


