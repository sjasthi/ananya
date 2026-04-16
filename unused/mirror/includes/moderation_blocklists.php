<?php

// Seed blocklists for puzzle generation safety. Keep entries lowercase where possible.
// This file intentionally includes only a minimal baseline and should be extended
// through curated review for each supported language.
// Deprecated at runtime: chat_api now reads config/blocklists/moderation_*.txt.
return [
    'english' => [
        'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'dick',
        'pussy', 'porn', 'nude', 'boob', 'penis', 'vagina',
        'rape', 'cum', 'sex', 'xxx', 'erotic', 'fetish',
    ],
    'telugu' => [
        // Keep this list script-native and classroom-safe maintained.
        'లంజ', 'ముండ', 'మడ్డ', 'పూకు', 'గుద్ద', 'దెంగయ్','నాకొడక','నీయమ్మ','దెంగేయ్'
    ],
    'hindi' => [
        // Keep this list script-native and classroom-safe maintained.
        'गाली', 'अश्लील', 'सेक्स', 'न्यूड', 'पोर्न',
    ],
    'gujarati' => [
        // Keep this list script-native and classroom-safe maintained.
        'ગાળો', 'અશ્લીલ', 'સેક્સ', 'ન્યુડ', 'પોર્ન',
    ],
    'malayalam' => [
        // Keep this list script-native and classroom-safe maintained.
        'അശ്ലീല', 'സെക്സ്', 'ന്യൂട്', 'പോൺ',
    ],
];
