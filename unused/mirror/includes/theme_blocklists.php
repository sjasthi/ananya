<?php

// Theme-level safety controls for puzzle generation.
// These are topic keywords, not profanity tokens.
// Deprecated at runtime: chat_api now reads config/blocklists/themes_*.txt.
return [
    'english' => [
        'recreational drugs',
        'drug abuse',
        'narcotic',
        'marijuana',
        'cannabis',
        'weed',
        'hashish',
        'cocaine',
        'heroin',
        'meth',
        'methamphetamine',
        'opium',
        'lsd',
        'pcp',
        'smoking',
        'tobacco',
        'cigarette',
        'vaping',
        'vape',
        'nicotine',
        'alcohol',
        'drinking',
        'drunk',
        'swear words',
        'curse words',
        'bad words',
        'profanity',
        'abusive language',

    ],
    'telugu' => [
        'మత్తు', 'మాదకద్రవ్య', 'గంజాయి',
        'ధూమపానం', 'సిగరెట్', 'వేపింగ్', 'నికోటిన్', 'మద్యం', 'మద్యపానం',
        'తిట్లు', 'అసభ్య పదాలు',
    ],
    'hindi' => [
        'नशा', 'मादक', 'गांजा',
        'धूम्रपान', 'सिगरेट', 'वेपिंग', 'निकोटीन', 'शराब', 'मद्यपान',
        'गाली', 'अशिष्ट शब्द',
    ],
    'gujarati' => [
        'નશો', 'માદક', 'ગાંજો',
        'ધૂમ્રપાન', 'સિગારેટ', 'વેપિંગ', 'નિકોટીન', 'દારૂ', 'મદ્યપાન',
        'ગાળો', 'અસભ્ય શબ્દો',
    ],
    'malayalam' => [
        'മയക്കുമരുന്ന്', 'കഞ്ചാവ്',
        'പുകവലി', 'സിഗരറ്റ്', 'വേപ്പിംഗ്', 'നിക്കോട്ടിൻ', 'മദ്യം', 'മദ്യപാനം',
        'തെറി', 'അശ്ലീല വാക്കുകൾ',
    ],
];
