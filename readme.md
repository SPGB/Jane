#Jane

Jane is **a contextual AI chatbot** created in PHP around 2011. It was my way of learning a lot of important concepts and methodology. This means that the code will be more messy and undocumented then usual. Please bear with me.

I'm sharing it partly for posterity, and partly for the sake of sharing. It seems a sad thing to leave on an old hard drive. 

###About

Whenever a new message is posted, an ajax request is sent to ajaxcheckjane.php, which calls this file: content.aware/jane.inc.php where the bulk of our work is done. The message is passed off into a function called Stimuli() which will attempt to pattern match the response. Her response to each stimuli is given a confidence, from 0-100, and she either responses based on her confidence.

Jane switches answers based on her mood, which changes based linguistic clues within the message. If there is a word that she doesn't know, she will ask you and enter a 'teaching' mode.

The majority of her responses are from her wordnet. She breaks down a sentence into an array of verbs/adjectives/nouns, and replies based on the sentence structure and words you used. Saying:

*I am cold.*

Might prompt her to ask:

*Why are you cold*.

There is still a lot of progress that could be made, and this was intended as a learning experience for me rather than a complete solution.

- - -

*Her voice was a whisper from the jewel in his ear.*
