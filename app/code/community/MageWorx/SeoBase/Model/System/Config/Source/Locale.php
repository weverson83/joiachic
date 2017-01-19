<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Model_System_Config_Source_Locale
{
    /**
     * ISO 639-1
     * @var array
     */
    protected $_options = array(
            array('value' => 'ab', 'label' => 'Abkhaz (ab)'),
            array('value' => 'aa', 'label' => 'Afar (aa)'),
            array('value' => 'af', 'label' => 'Afrikaans (af)'),
            array('value' => 'ak', 'label' => 'Akan (ak)'),
            array('value' => 'sq', 'label' => 'Albanian (sq)'),
            array('value' => 'am', 'label' => 'Amharic (am)'),
            array('value' => 'ar', 'label' => 'Arabic (ar)'),
            array('value' => 'an', 'label' => 'Aragonese (an)'),
            array('value' => 'hy', 'label' => 'Armenian (hy)'),
            array('value' => 'as', 'label' => 'Assamese (as)'),
            array('value' => 'av', 'label' => 'Avaric (av)'),
            array('value' => 'ae', 'label' => 'Avestan (ae)'),
            array('value' => 'ay', 'label' => 'Aymara (ay)'),
            array('value' => 'az', 'label' => 'Azerbaijani (az)'),
            array('value' => 'bm', 'label' => 'Bambara (bm)'),
            array('value' => 'ba', 'label' => 'Bashkir (ba)'),
            array('value' => 'eu', 'label' => 'Basque (eu)'),
            array('value' => 'be', 'label' => 'Belarusian (be)'),
            array('value' => 'bn', 'label' => 'Bengali, Bangla (bn)'),
            array('value' => 'bh', 'label' => 'Bihari (bh)'),
            array('value' => 'bi', 'label' => 'Bislama (bi)'),
            array('value' => 'bs', 'label' => 'Bosnian (bs)'),
            array('value' => 'br', 'label' => 'Breton (br)'),
            array('value' => 'bg', 'label' => 'Bulgarian (bg)'),
            array('value' => 'my', 'label' => 'Burmese (my)'),
            array('value' => 'ca', 'label' => 'Catalan (ca)'),
            array('value' => 'ch', 'label' => 'Chamorro (ch)'),
            array('value' => 'ce', 'label' => 'Chechen (ce)'),
            array('value' => 'ny', 'label' => 'Chichewa, Chewa, Nyanja (ny)'),
            array('value' => 'zh', 'label' => 'Chinese (zh)'),
            array('value' => 'cv', 'label' => 'Chuvash (cv)'),
            array('value' => 'kw', 'label' => 'Cornish (kw)'),
            array('value' => 'co', 'label' => 'Corsican (co)'),
            array('value' => 'cr', 'label' => 'Cree (cr)'),
            array('value' => 'hr', 'label' => 'Croatian (hr)'),
            array('value' => 'cs', 'label' => 'Czech (cs)'),
            array('value' => 'da', 'label' => 'Danish (da)'),
            array('value' => 'dv', 'label' => 'Divehi, Dhivehi, Maldivian (dv)'),
            array('value' => 'nl', 'label' => 'Dutch (nl)'),
            array('value' => 'dz', 'label' => 'Dzongkha (dz)'),
            array('value' => 'en', 'label' => 'English (en)'),
            array('value' => 'eo', 'label' => 'Esperanto (eo)'),
            array('value' => 'et', 'label' => 'Estonian (et)'),
            array('value' => 'ee', 'label' => 'Ewe (ee)'),
            array('value' => 'fo', 'label' => 'Faroese (fo)'),
            array('value' => 'fj', 'label' => 'Fijian (fj)'),
            array('value' => 'fi', 'label' => 'Finnish (fi)'),
            array('value' => 'fr', 'label' => 'French (fr)'),
            array('value' => 'ff', 'label' => 'Fula, Fulah, Pulaar, Pular (ff)'),
            array('value' => 'gl', 'label' => 'Galician (gl)'),
            array('value' => 'ka', 'label' => 'Georgian (ka)'),
            array('value' => 'de', 'label' => 'German (de)'),
            array('value' => 'el', 'label' => 'Greek (modern) (el)'),
            array('value' => 'gn', 'label' => 'Guaraní (gn)'),
            array('value' => 'gu', 'label' => 'Gujarati (gu)'),
            array('value' => 'ht', 'label' => 'Haitian, Haitian Creole (ht)'),
            array('value' => 'ha', 'label' => 'Hausa (ha)'),
            array('value' => 'he', 'label' => 'Hebrew  (he)'),
            array('value' => 'hz', 'label' => 'Herero (hz)'),
            array('value' => 'hi', 'label' => 'Hindi (hi)'),
            array('value' => 'ho', 'label' => 'Hiri Motu (ho)'),
            array('value' => 'hu', 'label' => 'Hungarian (hu)'),
            array('value' => 'ia', 'label' => 'Interlingua (ia)'),
            array('value' => 'id', 'label' => 'Indonesian (id)'),
            array('value' => 'ie', 'label' => 'Interlingue (ie)'),
            array('value' => 'ga', 'label' => 'Irish (ga)'),
            array('value' => 'ig', 'label' => 'Igbo (ig)'),
            array('value' => 'ik', 'label' => 'Inupiaq (ik)'),
            array('value' => 'io', 'label' => 'Ido (io)'),
            array('value' => 'is', 'label' => 'Icelandic (is)'),
            array('value' => 'it', 'label' => 'Italian (it)'),
            array('value' => 'iu', 'label' => 'Inuktitut (iu)'),
            array('value' => 'ja', 'label' => 'Japanese (ja)'),
            array('value' => 'jv', 'label' => 'Javanese (jv)'),
            array('value' => 'kl', 'label' => 'Kalaallisut, Greenlandic (kl)'),
            array('value' => 'kn', 'label' => 'Kannada (kn)'),
            array('value' => 'kr', 'label' => 'Kanuri (kr)'),
            array('value' => 'ks', 'label' => 'Kashmiri (ks)'),
            array('value' => 'kk', 'label' => 'Kazakh (kk)'),
            array('value' => 'km', 'label' => 'Khmer (km)'),
            array('value' => 'ki', 'label' => 'Kikuyu, Gikuyu (ki)'),
            array('value' => 'rw', 'label' => 'Kinyarwanda (rw)'),
            array('value' => 'ky', 'label' => 'Kyrgyz (ky)'),
            array('value' => 'kv', 'label' => 'Komi (kv)'),
            array('value' => 'kg', 'label' => 'Kongo (kg)'),
            array('value' => 'ko', 'label' => 'Korean (ko)'),
            array('value' => 'ku', 'label' => 'Kurdish (ku)'),
            array('value' => 'kj', 'label' => 'Kwanyama, Kuanyama (kj)'),
            array('value' => 'la', 'label' => 'Latin (la)'),
            array('value' => 'lb', 'label' => 'Luxembourgish, Letzeburgesch (lb)'),
            array('value' => 'lg', 'label' => 'Ganda (lg)'),
            array('value' => 'li', 'label' => 'Limburgish, Limburgan, Limburger (li)'),
            array('value' => 'ln', 'label' => 'Lingala (ln)'),
            array('value' => 'lo', 'label' => 'Lao (lo)'),
            array('value' => 'lt', 'label' => 'Lithuanian (lt)'),
            array('value' => 'lu', 'label' => 'Luba-Katanga (lu)'),
            array('value' => 'lv', 'label' => 'Latvian (lv)'),
            array('value' => 'gv', 'label' => 'Manx (gv)'),
            array('value' => 'mk', 'label' => 'Macedonian (mk)'),
            array('value' => 'mg', 'label' => 'Malagasy (mg)'),
            array('value' => 'ms', 'label' => 'Malay (ms)'),
            array('value' => 'ml', 'label' => 'Malayalam (ml)'),
            array('value' => 'mt', 'label' => 'Maltese (mt)'),
            array('value' => 'mi', 'label' => 'Māori (mi)'),
            array('value' => 'mr', 'label' => 'Marathi  (mr)'),
            array('value' => 'mh', 'label' => 'Marshallese (mh)'),
            array('value' => 'mn', 'label' => 'Mongolian (mn)'),
            array('value' => 'na', 'label' => 'Nauru (na)'),
            array('value' => 'nv', 'label' => 'Navajo, Navaho (nv)'),
            array('value' => 'nd', 'label' => 'Northern Ndebele (nd)'),
            array('value' => 'ne', 'label' => 'Nepali (ne)'),
            array('value' => 'ng', 'label' => 'Ndonga (ng)'),
            array('value' => 'nb', 'label' => 'Norwegian Bokmål (nb)'),
            array('value' => 'nn', 'label' => 'Norwegian Nynorsk (nn)'),
            array('value' => 'no', 'label' => 'Norwegian (no)'),
            array('value' => 'ii', 'label' => 'Nuosu (ii)'),
            array('value' => 'nr', 'label' => 'Southern Ndebele (nr)'),
            array('value' => 'oc', 'label' => 'Occitan (oc)'),
            array('value' => 'oj', 'label' => 'Ojibwe, Ojibwa (oj)'),
            array('value' => 'cu', 'label' => 'Old Church Slavonic, Old Bulgarian (cu)'),
            array('value' => 'om', 'label' => 'Oromo (om)'),
            array('value' => 'or', 'label' => 'Oriya (or)'),
            array('value' => 'os', 'label' => 'Ossetian, Ossetic (os)'),
            array('value' => 'pa', 'label' => 'Panjabi, Punjabi (pa)'),
            array('value' => 'pi', 'label' => 'Pāli (pi)'),
            array('value' => 'fa', 'label' => 'Persian (Farsi) (fa)'),
            array('value' => 'pl', 'label' => 'Polish (pl)'),
            array('value' => 'ps', 'label' => 'Pashto, Pushto (ps)'),
            array('value' => 'pt', 'label' => 'Portuguese (pt)'),
            array('value' => 'qu', 'label' => 'Quechua (qu)'),
            array('value' => 'rm', 'label' => 'Romansh (rm)'),
            array('value' => 'rn', 'label' => 'Kirundi (rn)'),
            array('value' => 'ro', 'label' => 'Romanian (ro)'),
            array('value' => 'ru', 'label' => 'Russian (ru)'),
            array('value' => 'sa', 'label' => 'Sanskrit  (sa)'),
            array('value' => 'sc', 'label' => 'Sardinian (sc)'),
            array('value' => 'sd', 'label' => 'Sindhi (sd)'),
            array('value' => 'se', 'label' => 'Northern Sami (se)'),
            array('value' => 'sm', 'label' => 'Samoan (sm)'),
            array('value' => 'sg', 'label' => 'Sango (sg)'),
            array('value' => 'sk', 'label' => 'Saraiki,Seraiki,Siraiki (sk)'),
            array('value' => 'sr', 'label' => 'Serbian (sr)'),
            array('value' => 'gd', 'label' => 'Scottish Gaelic, Gaelic (gd)'),
            array('value' => 'sn', 'label' => 'Shona (sn)'),
            array('value' => 'si', 'label' => 'Sinhala (si)'),
            array('value' => 'sk', 'label' => 'Slovak (sk)'),
            array('value' => 'sl', 'label' => 'Slovene (sl)'),
            array('value' => 'so', 'label' => 'Somali (so)'),
            array('value' => 'st', 'label' => 'Southern Sotho (st)'),
            array('value' => 'es', 'label' => 'Spanish (es)'),
            array('value' => 'su', 'label' => 'Sundanese (su)'),
            array('value' => 'sw', 'label' => 'Swahili (sw)'),
            array('value' => 'ss', 'label' => 'Swati (ss)'),
            array('value' => 'sv', 'label' => 'Swedish (sv)'),
            array('value' => 'ta', 'label' => 'Tamil (ta)'),
            array('value' => 'te', 'label' => 'Telugu (te)'),
            array('value' => 'tg', 'label' => 'Tajik (tg)'),
            array('value' => 'th', 'label' => 'Thai (th)'),
            array('value' => 'ti', 'label' => 'Tigrinya (ti)'),
            array('value' => 'bo', 'label' => 'Tibetan (bo)'),
            array('value' => 'tk', 'label' => 'Turkmen (tk)'),
            array('value' => 'tl', 'label' => 'Tagalog (tl)'),
            array('value' => 'tn', 'label' => 'Tswana (tn)'),
            array('value' => 'to', 'label' => 'Tonga  (to)'),
            array('value' => 'tr', 'label' => 'Turkish (tr)'),
            array('value' => 'ts', 'label' => 'Tsonga (ts)'),
            array('value' => 'tt', 'label' => 'Tatar (tt)'),
            array('value' => 'tw', 'label' => 'Twi (tw)'),
            array('value' => 'ty', 'label' => 'Tahitian (ty)'),
            array('value' => 'ug', 'label' => 'Uyghur (ug)'),
            array('value' => 'uk', 'label' => 'Ukrainian (uk)'),
            array('value' => 'ur', 'label' => 'Urdu (ur)'),
            array('value' => 'uz', 'label' => 'Uzbek (uz)'),
            array('value' => 've', 'label' => 'Venda (ve)'),
            array('value' => 'vi', 'label' => 'Vietnamese (vi)'),
            array('value' => 'vo', 'label' => 'Volapük (vo)'),
            array('value' => 'wa', 'label' => 'Walloon (wa)'),
            array('value' => 'cy', 'label' => 'Welsh (cy)'),
            array('value' => 'wo', 'label' => 'Wolof (wo)'),
            array('value' => 'fy', 'label' => 'Western Frisian (fy)'),
            array('value' => 'xh', 'label' => 'Xhosa (xh)'),
            array('value' => 'yi', 'label' => 'Yiddish (yi)'),
            array('value' => 'yo', 'label' => 'Yoruba (yo)'),
            array('value' => 'za', 'label' => 'Zhuang, Chuang (za)'),
            array('value' => 'zu', 'label' => 'Zulu (zu)')
    );


    public function toOptionArray()
    {
        return $this->_options;
    }
}