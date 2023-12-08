<?php // path: config/cfg_openAIConfig.php

const __OPEN_AI_API_URL__ = 'https://api.openai.com/v1/completions';
const __OPEN_AI_API_KEY__ = 'Your_OPEN_AI_API_Key';
const __OPEN_AI_MODEL__ = 'gpt-3.5-turbo-instruct';
const __OPEN_AI_TEMPERATURE__ = 0.7;
const __OPEN_AI_MAX_TOKENS__ = 150;
const __OPEN_AI_TOP_P__ = 1;
const __OPEN_AI_FREQUENCY_PENALTY__ = 0.5;
const __OPEN_AI_PRESENCE_PENALTY__ = 0.5;
const __OPEN_AI_STOP__ = ["Human:", "AI:"];