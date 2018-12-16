
<?php
echo ("<pre>");
require_once "functions.php";
exec('unset DYLD_LIBRARY_PATH ;');
putenv('DYLD_LIBRARY_PATH');
putenv('DYLD_LIBRARY_PATH=/usr/bin');
date_default_timezone_set("GMT");
echo "The time is " . date("Y-m-d H:i:s") . '   ' . time();
$shouldStopMeme = false;
//86400 is a day in seconds
$numberOfVideosToUploadADay = 3;
$numberOfInsaneVideostoUploadADay = 0;
$timeAllowedBetweenVideosFullStop = 86400 / $numberOfVideosToUploadADay;
$ffmpeg = './ffmpeg';
$enablePosting = 1;
$disableMemeStream = true;
$insaneMode = false;//start off false, m ake it true below
$enableCommentingShoutout = 1;
$testingMode = 0;
$todaysDate = getdate(date("U"));
$todaysMonthCaps = strtoupper($todaysDate['month']);
$todaysYearCaps = strtoupper($todaysDate['year']);
$shouldRenameVideosToNewTodaysDate = 0;
$fractionOfWayThroughVideoToGetThumbnail = 3 / 5;//the problem is if it is too early the short videos are ususally porttrait so we get the bars in the thumbnail.
$finalVidsDir = "/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/finalVids";
$arrayOfIndividualVideosToUpload = array();
$tempDownloadsMemes = scandir('/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/tempDownloadedMemes');
$memesAlreadyJudgedForLength = file("memesAlreadyJudgedForLength.txt", FILE_IGNORE_NEW_LINES);
$shouldTryToPostIndividualMemes = false;
$OAUTH2_CLIENT_ID = 'your id';
$OAUTH2_CLIENT_SECRET = 'your secret';
$refreshToken = '1/your refresh token'; //daily meme comps

$nameOfMemesToUploadFile = 'memesToUpload.txt';

if (!$enablePosting) {
    echo 'i switched off posting ';
    $shouldStopMeme = true;
}

echo 'this is the upload bot';

//check if there are any videos in the insane folder to upload, and upload them all, then upload normal videos

$insaneFolder = scandir_only_wanted_files("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/finalVidsInsane");
if (count($insaneFolder) == 0) {
    echo "we have no outstanding insane memes to post so doing so";
    $insaneMode = false;

}

if ($disableMemeStream) {//not necessarily
    echo "disable meme stream so turning on insane mode";
  //$insaneMode = true;
}

if ($insaneMode) {
    $refreshToken = '1/aTDpENdQjvBlBY3ZnVuc_sVRq0a8pZFd-5_BW9AcQ8M';//set this
//  $memesToUpload = file('/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/memesToUploadInsane.txt', FILE_IGNORE_NEW_LINES);
//  $memesToUpload = scandir_only_wanted_files("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/finalVidsInsane");//the reason we don't do this is coz the video might still be generating while we try to upload ffs
    $nameOfMemesToUploadFile = "memesToUploadInsane.txt";
    $timeAllowedBetweenVideosFullStop = 86400 / $numberOfInsaneVideostoUploadADay;
  //$timeAllowedBetweenVideosFullStop = 0;

    $finalVidsDir = "/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/finalVidsInsane";
}

//we no longer use the txt file to keep track of which memes to upload as it is weird and unreliable for some reason. now we scan the dir directly
//$memesToUpload = file("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/$nameOfMemesToUploadFile", FILE_IGNORE_NEW_LINES);
$memesToUpload = scandir_only_wanted_files($finalVidsDir);

foreach ($memesToUpload as $key => $value) {
    if (explode("_", $value)[0] == 'unfinished') {
        echo 'found unfinished so unsetting from memes to upload';
        unset($memesToUpload[$key]);
    }
}
$memesToUpload = array_values($memesToUpload);
printArray($memesToUpload, "meme to upload");

$refreshRate = 999;//not too low it keeps checknig vids to comment on id get rate lim - 500 gives quota limit lol
header("refresh:$refreshRate;url= index.php");
$randomNumber = mt_rand(2, 100) / 100;

// scan the array of comments on a vid, and if it is from me and is equal to autoCommentMessage, then do not comment on it again.
// Make sure to inject the channel name between the first and second phrase later

$potentialFirstPhrase = array(
    "Hey",
    "Hello",
    "Sup",
    "What's up",
    "Yo",
    "Yo yo",
    "Hi",
    "Henlo",
    "Wuddup",
    "Howdy"
);

// Make sure to inject the channel name between the first and second phrase later

$potentialSecondPhrase = array(
    "how are you?",
    "how's it going?",
    "how's life?",
    "how is life?",
    "how you doing?",
    "how are you doing?",
    "how u doing?",
    "how is it going?",
    "how's it going my dude?"
);
$potentialThirdPhrase = array(
    "I really liked the video",
    "I enjoyed the video a lot",
    "This was a great video",
    "Nice video",
    "Nice vid",
    "Nice vid dude",
    "I enjoyed the video",
    "Great video",
    "Great vid",
    "Amazing video",
    "Amazing vid",
);

// add 'and'

$potentialFourthPhrase = array(
    "I checked out the channels you shouted out and subbed to all of them",
    "I subbed to all the channels you shouted out",
    "the channels you shouted out were great!",
    "I loved the channels you shouted out",
    "I subscribed to all the channels you shouted out",
    "I subscribed to all the channels you mentioned in the vid",
    "I subbed to all the channels you mentioned",
    "I subbed to the channels you showed us in the vid",
    "I thought all the channels you shouted out in the vid were great",
);
$potentialFifthPhrase = array(
    "I keep trying to win the shoutout competition",
    "Hopefully one day i'll win",
    "I hope you'll shoutout my channel one day",
    "I would love it if you shouted out my channel",
    "I work really hard on my channel and hope you will shout it out",
    "I put a lot of effort into my videos and wish I got more subs",
    "It would make my week if you shouted out my channel",
    "If you shouted me out you would be a god",
    "I need the motivation to keep going on youtube by having more subs",
    "If you could shout me out I would love you forever",
    "Please shout out my channel I need some more subs",
    "I'm really low on subs and need more subscribers",
    "Can you do me a favour and please shout me out",
    "My channel doesn't have the chance to grow with these new algorithms and I need a shoutout",
    "It is impossible to grow in 2017 so could you please please shout out my channel",
    "Could you please do me the biggest favour and shout out my channel?",
);
$potentialSixthPhrase = array(
    "It's my dream to get big on youtube",
    "It is my dream to become a full time youtuber",
    "I really hope that one day I can do youtube full time",
    "I really want youtube to become my job one day",
    "My dream is to reach 10,000 subscribers, but i'm so far off right now",
    "Maybe one day i'll hit it big on youtube and achieve my goal",
    "Hopefully I can recah my goal of 10,000 subscribers one day"
);


// $potentialTextMemes = array(
//     "The FitnessGram™ Pacer Test is a multistage aerobic capacity test that progressively gets more difficult as it continues. The 20 meter pacer test will begin in 30 seconds. Line up at the start.",
//     "T😂h😂e😂 😂F😂i😂t😂n😂e😂s😂s😂G😂r😂a😂m😂™😂 😂P😂a😂c😂e😂r😂 😂T😂e😂s😂t😂 😂i😂s😂 😂a😂 😂m😂u😂l😂t😂i😂s😂t😂a😂g😂e😂 😂a😂e😂r😂o😂b😂i😂c😂 😂c😂a😂p😂a😂c😂i😂t😂y😂 😂t😂e😂s😂t😂 😂t😂h😂a😂t😂 😂p😂r😂o😂g😂r😂e😂s😂s😂i😂v😂e😂l😂y😂 😂g😂e😂t😂s😂 😂m😂o😂r😂e😂 😂d😂i😂f😂f😂i😂c😂u😂l😂t😂 😂a😂s😂 😂i😂t😂 😂c😂o😂n😂t😂i😂n😂u😂e😂s😂.😂 😂T😂h😂e😂 😂2😂0😂 😂m😂e😂t😂e😂r😂 😂p😂a😂c😂e😂r😂 😂t😂e😂s😂t😂 😂w😂i😂l😂l😂 😂b😂e😂g😂i😂n😂 😂i😂n😂 😂3😂0😂 😂s😂e😂c😂o😂n😂d😂s😂.😂 😂L😂i😂n😂e😂 😂u😂p😂 😂a😂t😂 😂t😂h😂e😂 😂s😂t😂a😂r😂t😂.",
//     "T🖕h🖕e🖕 🖕F🖕i🖕t🖕n🖕e🖕s🖕s🖕G🖕r🖕a🖕m🖕™🖕 🖕P🖕a🖕c🖕e🖕r🖕 🖕T🖕e🖕s🖕t🖕 🖕i🖕s🖕 🖕a🖕 🖕m🖕u🖕l🖕t🖕i🖕s🖕t🖕a🖕g🖕e🖕 🖕a🖕e🖕r🖕o🖕b🖕i🖕c🖕 🖕c🖕a🖕p🖕a🖕c🖕i🖕t🖕y🖕 🖕t🖕e🖕s🖕t🖕 🖕t🖕h🖕a🖕t🖕 🖕p🖕r🖕o🖕g🖕r🖕e🖕s🖕s🖕i🖕v🖕e🖕l🖕y🖕 🖕g🖕e🖕t🖕s🖕 🖕m🖕o🖕r🖕e🖕 🖕d🖕i🖕f🖕f🖕i🖕c🖕u🖕l🖕t🖕 🖕a🖕s🖕 🖕i🖕t🖕 🖕c🖕o🖕n🖕t🖕i🖕n🖕u🖕e🖕s🖕.🖕 🖕T🖕h🖕e🖕 🖕2🖕0🖕 🖕m🖕e🖕t🖕e🖕r🖕 🖕p🖕a🖕c🖕e🖕r🖕 🖕t🖕e🖕s🖕t🖕 🖕w🖕i🖕l🖕l🖕 🖕b🖕e🖕g🖕i🖕n🖕 🖕i🖕n🖕 🖕3🖕0🖕 🖕s🖕e🖕c🖕o🖕n🖕d🖕s🖕.🖕 🖕L🖕i🖕n🖕e🖕 🖕u🖕p🖕 🖕a🖕t🖕 🖕t🖕h🖕e🖕 🖕s🖕t🖕a🖕r🖕t🖕.",
//     "T😡h😡e😡 😡F😡i😡t😡n😡e😡s😡s😡G😡r😡a😡m😡™😡 😡P😡a😡c😡e😡r😡 😡T😡e😡s😡t😡 😡i😡s😡 😡a😡 😡m😡u😡l😡t😡i😡s😡t😡a😡g😡e😡 😡a😡e😡r😡o😡b😡i😡c😡 😡c😡a😡p😡a😡c😡i😡t😡y😡 😡t😡e😡s😡t😡 😡t😡h😡a😡t😡 😡p😡r😡o😡g😡r😡e😡s😡s😡i😡v😡e😡l😡y😡 😡g😡e😡t😡s😡 😡m😡o😡r😡e😡 😡d😡i😡f😡f😡i😡c😡u😡l😡t😡 😡a😡s😡 😡i😡t😡 😡c😡o😡n😡t😡i😡n😡u😡e😡s😡.😡 😡T😡h😡e😡 😡2😡0😡 😡m😡e😡t😡e😡r😡 😡p😡a😡c😡e😡r😡 😡t😡e😡s😡t😡 😡w😡i😡l😡l😡 😡b😡e😡g😡i😡n😡 😡i😡n😡 😡3😡0😡 😡s😡e😡c😡o😡n😡d😡s😡.😡 😡L😡i😡n😡e😡 😡u😡p😡 😡a😡t😡 😡t😡h😡e😡 😡s😡t😡a😡r😡t😡.",
//     "T💩h💩e💩 💩F💩i💩t💩n💩e💩s💩s💩G💩r💩a💩m💩™💩 💩P💩a💩c💩e💩r💩 💩T💩e💩s💩t💩 💩i💩s💩 💩a💩 💩m💩u💩l💩t💩i💩s💩t💩a💩g💩e💩 💩a💩e💩r💩o💩b💩i💩c💩 💩c💩a💩p💩a💩c💩i💩t💩y💩 💩t💩e💩s💩t💩 💩t💩h💩a💩t💩 💩p💩r💩o💩g💩r💩e💩s💩s💩i💩v💩e💩l💩y💩 💩g💩e💩t💩s💩 💩m💩o💩r💩e💩 💩d💩i💩f💩f💩i💩c💩u💩l💩t💩 💩a💩s💩 💩i💩t💩 💩c💩o💩n💩t💩i💩n💩u💩e💩s💩.💩 💩T💩h💩e💩 💩2💩0💩 💩m💩e💩t💩e💩r💩 💩p💩a💩c💩e💩r💩 💩t💩e💩s💩t💩 💩w💩i💩l💩l💩 💩b💩e💩g💩i💩n💩 💩i💩n💩 💩3💩0💩 💩s💩e💩c💩o💩n💩d💩s💩.💩 💩L💩i💩n💩e💩 💩u💩p💩 💩a💩t💩 💩t💩h💩e💩 💩s💩t💩a💩r💩t💩.",
//     "T👉👌h👉👌e👉👌 👉👌F👉👌i👉👌t👉👌n👉👌e👉👌s👉👌s👉👌G👉👌r👉👌a👉👌m👉👌™👉👌 👉👌P👉👌a👉👌c👉👌e👉👌r👉👌 👉👌T👉👌e👉👌s👉👌t👉👌 👉👌i👉👌s👉👌 👉👌a👉👌 👉👌m👉👌u👉👌l👉👌t👉👌i👉👌s👉👌t👉👌a👉👌g👉👌e👉👌 👉👌a👉👌e👉👌r👉👌o👉👌b👉👌i👉👌c👉👌 👉👌c👉👌a👉👌p👉👌a👉👌c👉👌i👉👌t👉👌y👉👌 👉👌t👉👌e👉👌s👉👌t👉👌 👉👌t👉👌h👉👌a👉👌t👉👌 👉👌p👉👌r👉👌o👉👌g👉👌r👉👌e👉👌s👉👌s👉👌i👉👌v👉👌e👉👌l👉👌y👉👌 👉👌g👉👌e👉👌t👉👌s👉👌 👉👌m👉👌o👉👌r👉👌e👉👌 👉👌d👉👌i👉👌f👉👌f👉👌i👉👌c👉👌u👉👌l👉👌t👉👌 👉👌a👉👌s👉👌 👉👌i👉👌t👉👌 👉👌c👉👌o👉👌n👉👌t👉👌i👉👌n👉👌u👉👌e👉👌s👉👌.👉👌 👉👌T👉👌h👉👌e👉👌 👉👌2👉👌0👉👌 👉👌m👉👌e👉👌t👉👌e👉👌r👉👌 👉👌p👉👌a👉👌c👉👌e👉👌r👉👌 👉👌t👉👌e👉👌s👉👌t👉👌 👉👌w👉👌i👉👌l👉👌l👉👌 👉👌b👉👌e👉👌g👉👌i👉👌n👉👌 👉👌i👉👌n👉👌 👉👌3👉👌0👉👌 👉👌s👉👌e👉👌c👉👌o👉👌n👉👌d👉👌s👉👌.👉👌 👉👌L👉👌i👉👌n👉👌e👉👌 👉👌u👉👌p👉👌 👉👌a👉👌t👉👌 👉👌t👉👌h👉👌e👉👌 👉👌s👉👌t👉👌a👉👌r👉👌t👉👌.",
//     "T👀h👀e👀 👀F👀i👀t👀n👀e👀s👀s👀G👀r👀a👀m👀™👀 👀P👀a👀c👀e👀r👀 👀T👀e👀s👀t👀 👀i👀s👀 👀a👀 👀m👀u👀l👀t👀i👀s👀t👀a👀g👀e👀 👀a👀e👀r👀o👀b👀i👀c👀 👀c👀a👀p👀a👀c👀i👀t👀y👀 👀t👀e👀s👀t👀 👀t👀h👀a👀t👀 👀p👀r👀o👀g👀r👀e👀s👀s👀i👀v👀e👀l👀y👀 👀g👀e👀t👀s👀 👀m👀o👀r👀e👀 👀d👀i👀f👀f👀i👀c👀u👀l👀t👀 👀a👀s👀 👀i👀t👀 👀c👀o👀n👀t👀i👀n👀u👀e👀s👀.👀 👀T👀h👀e👀 👀2👀0👀 👀m👀e👀t👀e👀r👀 👀p👀a👀c👀e👀r👀 👀t👀e👀s👀t👀 👀w👀i👀l👀l👀 👀b👀e👀g👀i👀n👀 👀i👀n👀 👀3👀0👀 👀s👀e👀c👀o👀n👀d👀s👀.👀 👀L👀i👀n👀e👀 👀u👀p👀 👀a👀t👀 👀t👀h👀e👀 👀s👀t👀a👀r👀t👀.",
//     "T🏃🏿h🏃🏿e🏃🏿 🏃🏿F🏃🏿i🏃🏿t🏃🏿n🏃🏿e🏃🏿s🏃🏿s🏃🏿G🏃🏿r🏃🏿a🏃🏿m🏃🏿™🏃🏿 🏃🏿P🏃🏿a🏃🏿c🏃🏿e🏃🏿r🏃🏿 🏃🏿T🏃🏿e🏃🏿s🏃🏿t🏃🏿 🏃🏿i🏃🏿s🏃🏿 🏃🏿a🏃🏿 🏃🏿m🏃🏿u🏃🏿l🏃🏿t🏃🏿i🏃🏿s🏃🏿t🏃🏿a🏃🏿g🏃🏿e🏃🏿 🏃🏿a🏃🏿e🏃🏿r🏃🏿o🏃🏿b🏃🏿i🏃🏿c🏃🏿 🏃🏿c🏃🏿a🏃🏿p🏃🏿a🏃🏿c🏃🏿i🏃🏿t🏃🏿y🏃🏿 🏃🏿t🏃🏿e🏃🏿s🏃🏿t🏃🏿 🏃🏿t🏃🏿h🏃🏿a🏃🏿t🏃🏿 🏃🏿p🏃🏿r🏃🏿o🏃🏿g🏃🏿r🏃🏿e🏃🏿s🏃🏿s🏃🏿i🏃🏿v🏃🏿e🏃🏿l🏃🏿y🏃🏿 🏃🏿g🏃🏿e🏃🏿t🏃🏿s🏃🏿 🏃🏿m🏃🏿o🏃🏿r🏃🏿e🏃🏿 🏃🏿d🏃🏿i🏃🏿f🏃🏿f🏃🏿i🏃🏿c🏃🏿u🏃🏿l🏃🏿t🏃🏿 🏃🏿a🏃🏿s🏃🏿 🏃🏿i🏃🏿t🏃🏿 🏃🏿c🏃🏿o🏃🏿n🏃🏿t🏃🏿i🏃🏿n🏃🏿u🏃🏿e🏃🏿s🏃🏿.🏃🏿 🏃🏿T🏃🏿h🏃🏿e🏃🏿 🏃🏿2🏃🏿0🏃🏿 🏃🏿m🏃🏿e🏃🏿t🏃🏿e🏃🏿r🏃🏿 🏃🏿p🏃🏿a🏃🏿c🏃🏿e🏃🏿r🏃🏿 🏃🏿t🏃🏿e🏃🏿s🏃🏿t🏃🏿 🏃🏿w🏃🏿i🏃🏿l🏃🏿l🏃🏿 🏃🏿b🏃🏿e🏃🏿g🏃🏿i🏃🏿n🏃🏿 🏃🏿i🏃🏿n🏃🏿 🏃🏿3🏃🏿0🏃🏿 🏃🏿s🏃🏿e🏃🏿c🏃🏿o🏃🏿n🏃🏿d🏃🏿s🏃🏿.🏃🏿 🏃🏿L🏃🏿i🏃🏿n🏃🏿e🏃🏿 🏃🏿u🏃🏿p🏃🏿 🏃🏿a🏃🏿t🏃🏿 🏃🏿t🏃🏿h🏃🏿e🏃🏿 🏃🏿s🏃🏿t🏃🏿a🏃🏿r🏃🏿t🏃🏿.",
//     "T🐖h🐖e🐖 🐖F🐖i🐖t🐖n🐖e🐖s🐖s🐖G🐖r🐖a🐖m🐖™🐖 🐖P🐖a🐖c🐖e🐖r🐖 🐖T🐖e🐖s🐖t🐖 🐖i🐖s🐖 🐖a🐖 🐖m🐖u🐖l🐖t🐖i🐖s🐖t🐖a🐖g🐖e🐖 🐖a🐖e🐖r🐖o🐖b🐖i🐖c🐖 🐖c🐖a🐖p🐖a🐖c🐖i🐖t🐖y🐖 🐖t🐖e🐖s🐖t🐖 🐖t🐖h🐖a🐖t🐖 🐖p🐖r🐖o🐖g🐖r🐖e🐖s🐖s🐖i🐖v🐖e🐖l🐖y🐖 🐖g🐖e🐖t🐖s🐖 🐖m🐖o🐖r🐖e🐖 🐖d🐖i🐖f🐖f🐖i🐖c🐖u🐖l🐖t🐖 🐖a🐖s🐖 🐖i🐖t🐖 🐖c🐖o🐖n🐖t🐖i🐖n🐖u🐖e🐖s🐖.🐖 🐖T🐖h🐖e🐖 🐖2🐖0🐖 🐖m🐖e🐖t🐖e🐖r🐖 🐖p🐖a🐖c🐖e🐖r🐖 🐖t🐖e🐖s🐖t🐖 🐖w🐖i🐖l🐖l🐖 🐖b🐖e🐖g🐖i🐖n🐖 🐖i🐖n🐖 🐖3🐖0🐖 🐖s🐖e🐖c🐖o🐖n🐖d🐖s🐖.🐖 🐖L🐖i🐖n🐖e🐖 🐖u🐖p🐖 🐖a🐖t🐖 🐖t🐖h🐖e🐖 🐖s🐖t🐖a🐖r🐖t🐖.",
//     "T💦h💦e💦 💦F💦i💦t💦n💦e💦s💦s💦G💦r💦a💦m💦™💦 💦P💦a💦c💦e💦r💦 💦T💦e💦s💦t💦 💦i💦s💦 💦a💦 💦m💦u💦l💦t💦i💦s💦t💦a💦g💦e💦 💦a💦e💦r💦o💦b💦i💦c💦 💦c💦a💦p💦a💦c💦i💦t💦y💦 💦t💦e💦s💦t💦 💦t💦h💦a💦t💦 💦p💦r💦o💦g💦r💦e💦s💦s💦i💦v💦e💦l💦y💦 💦g💦e💦t💦s💦 💦m💦o💦r💦e💦 💦d💦i💦f💦f💦i💦c💦u💦l💦t💦 💦a💦s💦 💦i💦t💦 💦c💦o💦n💦t💦i💦n💦u💦e💦s💦.💦 💦T💦h💦e💦 💦2💦0💦 💦m💦e💦t💦e💦r💦 💦p💦a💦c💦e💦r💦 💦t💦e💦s💦t💦 💦w💦i💦l💦l💦 💦b💦e💦g💦i💦n💦 💦i💦n💦 💦3💦0💦 💦s💦e💦c💦o💦n💦d💦s💦.💦 💦L💦i💦n💦e💦 💦u💦p💦 💦a💦t💦 💦t💦h💦e💦 💦s💦t💦a💦r💦t💦.",
//     "T💦🍌h💦🍌e💦🍌 💦🍌F💦🍌i💦🍌t💦🍌n💦🍌e💦🍌s💦🍌s💦🍌G💦🍌r💦🍌a💦🍌m💦🍌™💦🍌 💦🍌P💦🍌a💦🍌c💦🍌e💦🍌r💦🍌 💦🍌T💦🍌e💦🍌s💦🍌t💦🍌 💦🍌i💦🍌s💦🍌 💦🍌a💦🍌 💦🍌m💦🍌u💦🍌l💦🍌t💦🍌i💦🍌s💦🍌t💦🍌a💦🍌g💦🍌e💦🍌 💦🍌a💦🍌e💦🍌r💦🍌o💦🍌b💦🍌i💦🍌c💦🍌 💦🍌c💦🍌a💦🍌p💦🍌a💦🍌c💦🍌i💦🍌t💦🍌y💦🍌 💦🍌t💦🍌e💦🍌s💦🍌t💦🍌 💦🍌t💦🍌h💦🍌a💦🍌t💦🍌 💦🍌p💦🍌r💦🍌o💦🍌g💦🍌r💦🍌e💦🍌s💦🍌s💦🍌i💦🍌v💦🍌e💦🍌l💦🍌y💦🍌 💦🍌g💦🍌e💦🍌t💦🍌s💦🍌 💦🍌m💦🍌o💦🍌r💦🍌e💦🍌 💦🍌d💦🍌i💦🍌f💦🍌f💦🍌i💦🍌c💦🍌u💦🍌l💦🍌t💦🍌 💦🍌a💦🍌s💦🍌 💦🍌i💦🍌t💦🍌 💦🍌c💦🍌o💦🍌n💦🍌t💦🍌i💦🍌n💦🍌u💦🍌e💦🍌s💦🍌.💦🍌 💦🍌T💦🍌h💦🍌e💦🍌 💦🍌2💦🍌0💦🍌 💦🍌m💦🍌e💦🍌t💦🍌e💦🍌r💦🍌 💦🍌p💦🍌a💦🍌c💦🍌e💦🍌r💦🍌 💦🍌t💦🍌e💦🍌s💦🍌t💦🍌 💦🍌w💦🍌i💦🍌l💦🍌l💦🍌 💦🍌b💦🍌e💦🍌g💦🍌i💦🍌n💦🍌 💦🍌i💦🍌n💦🍌 💦🍌3💦🍌0💦🍌 💦🍌s💦🍌e💦🍌c💦🍌o💦🍌n💦🍌d💦🍌s💦🍌.💦🍌 💦🍌L💦🍌i💦🍌n💦🍌e💦🍌 💦🍌u💦🍌p💦🍌 💦🍌a💦🍌t💦🍌 💦🍌t💦🍌h💦🍌e💦🍌 💦🍌s💦🍌t💦🍌a💦🍌r💦🍌t💦🍌.",
//     "T🍌h🍌e🍌 🍌F🍌i🍌t🍌n🍌e🍌s🍌s🍌G🍌r🍌a🍌m🍌™🍌 🍌P🍌a🍌c🍌e🍌r🍌 🍌T🍌e🍌s🍌t🍌 🍌i🍌s🍌 🍌a🍌 🍌m🍌u🍌l🍌t🍌i🍌s🍌t🍌a🍌g🍌e🍌 🍌a🍌e🍌r🍌o🍌b🍌i🍌c🍌 🍌c🍌a🍌p🍌a🍌c🍌i🍌t🍌y🍌 🍌t🍌e🍌s🍌t🍌 🍌t🍌h🍌a🍌t🍌 🍌p🍌r🍌o🍌g🍌r🍌e🍌s🍌s🍌i🍌v🍌e🍌l🍌y🍌 🍌g🍌e🍌t🍌s🍌 🍌m🍌o🍌r🍌e🍌 🍌d🍌i🍌f🍌f🍌i🍌c🍌u🍌l🍌t🍌 🍌a🍌s🍌 🍌i🍌t🍌 🍌c🍌o🍌n🍌t🍌i🍌n🍌u🍌e🍌s🍌.🍌 🍌T🍌h🍌e🍌 🍌2🍌0🍌 🍌m🍌e🍌t🍌e🍌r🍌 🍌p🍌a🍌c🍌e🍌r🍌 🍌t🍌e🍌s🍌t🍌 🍌w🍌i🍌l🍌l🍌 🍌b🍌e🍌g🍌i🍌n🍌 🍌i🍌n🍌 🍌3🍌0🍌 🍌s🍌e🍌c🍌o🍌n🍌d🍌s🍌.🍌 🍌L🍌i🍌n🍌e🍌 🍌u🍌p🍌 🍌a🍌t🍌 🍌t🍌h🍌e🍌 🍌s🍌t🍌a🍌r🍌t🍌.",
//     "T🥑h🥑e🥑 🥑F🥑i🥑t🥑n🥑e🥑s🥑s🥑G🥑r🥑a🥑m🥑™🥑 🥑P🥑a🥑c🥑e🥑r🥑 🥑T🥑e🥑s🥑t🥑 🥑i🥑s🥑 🥑a🥑 🥑m🥑u🥑l🥑t🥑i🥑s🥑t🥑a🥑g🥑e🥑 🥑a🥑e🥑r🥑o🥑b🥑i🥑c🥑 🥑c🥑a🥑p🥑a🥑c🥑i🥑t🥑y🥑 🥑t🥑e🥑s🥑t🥑 🥑t🥑h🥑a🥑t🥑 🥑p🥑r🥑o🥑g🥑r🥑e🥑s🥑s🥑i🥑v🥑e🥑l🥑y🥑 🥑g🥑e🥑t🥑s🥑 🥑m🥑o🥑r🥑e🥑 🥑d🥑i🥑f🥑f🥑i🥑c🥑u🥑l🥑t🥑 🥑a🥑s🥑 🥑i🥑t🥑 🥑c🥑o🥑n🥑t🥑i🥑n🥑u🥑e🥑s🥑.🥑 🥑T🥑h🥑e🥑 🥑2🥑0🥑 🥑m🥑e🥑t🥑e🥑r🥑 🥑p🥑a🥑c🥑e🥑r🥑 🥑t🥑e🥑s🥑t🥑 🥑w🥑i🥑l🥑l🥑 🥑b🥑e🥑g🥑i🥑n🥑 🥑i🥑n🥑 🥑3🥑0🥑 🥑s🥑e🥑c🥑o🥑n🥑d🥑s🥑.🥑 🥑L🥑i🥑n🥑e🥑 🥑u🥑p🥑 🥑a🥑t🥑 🥑t🥑h🥑e🥑 🥑s🥑t🥑a🥑r🥑t🥑.",
//     "T❤️h❤️e❤️ ❤️F❤️i❤️t❤️n❤️e❤️s❤️s❤️G❤️r❤️a❤️m❤️™❤️ ❤️P❤️a❤️c❤️e❤️r❤️ ❤️T❤️e❤️s❤️t❤️ ❤️i❤️s❤️ ❤️a❤️ ❤️m❤️u❤️l❤️t❤️i❤️s❤️t❤️a❤️g❤️e❤️ ❤️a❤️e❤️r❤️o❤️b❤️i❤️c❤️ ❤️c❤️a❤️p❤️a❤️c❤️i❤️t❤️y❤️ ❤️t❤️e❤️s❤️t❤️ ❤️t❤️h❤️a❤️t❤️ ❤️p❤️r❤️o❤️g❤️r❤️e❤️s❤️s❤️i❤️v❤️e❤️l❤️y❤️ ❤️g❤️e❤️t❤️s❤️ ❤️m❤️o❤️r❤️e❤️ ❤️d❤️i❤️f❤️f❤️i❤️c❤️u❤️l❤️t❤️ ❤️a❤️s❤️ ❤️i❤️t❤️ ❤️c❤️o❤️n❤️t❤️i❤️n❤️u❤️e❤️s❤️.❤️ ❤️T❤️h❤️e❤️ ❤️2❤️0❤️ ❤️m❤️e❤️t❤️e❤️r❤️ ❤️p❤️a❤️c❤️e❤️r❤️ ❤️t❤️e❤️s❤️t❤️ ❤️w❤️i❤️l❤️l❤️ ❤️b❤️e❤️g❤️i❤️n❤️ ❤️i❤️n❤️ ❤️3❤️0❤️ ❤️s❤️e❤️c❤️o❤️n❤️d❤️s❤️.❤️ ❤️L❤️i❤️n❤️e❤️ ❤️u❤️p❤️ ❤️a❤️t❤️ ❤️t❤️h❤️e❤️ ❤️s❤️t❤️a❤️r❤️t❤️.",
//     "T🅱h🅱e🅱 🅱F🅱i🅱t🅱n🅱e🅱s🅱s🅱G🅱r🅱a🅱m🅱™🅱 🅱P🅱a🅱c🅱e🅱r🅱 🅱T🅱e🅱s🅱t🅱 🅱i🅱s🅱 🅱a🅱 🅱m🅱u🅱l🅱t🅱i🅱s🅱t🅱a🅱g🅱e🅱 🅱a🅱e🅱r🅱o🅱b🅱i🅱c🅱 🅱c🅱a🅱p🅱a🅱c🅱i🅱t🅱y🅱 🅱t🅱e🅱s🅱t🅱 🅱t🅱h🅱a🅱t🅱 🅱p🅱r🅱o🅱g🅱r🅱e🅱s🅱s🅱i🅱v🅱e🅱l🅱y🅱 🅱g🅱e🅱t🅱s🅱 🅱m🅱o🅱r🅱e🅱 🅱d🅱i🅱f🅱f🅱i🅱c🅱u🅱l🅱t🅱 🅱a🅱s🅱 🅱i🅱t🅱 🅱c🅱o🅱n🅱t🅱i🅱n🅱u🅱e🅱s🅱.🅱 🅱T🅱h🅱e🅱 🅱2🅱0🅱 🅱m🅱e🅱t🅱e🅱r🅱 🅱p🅱a🅱c🅱e🅱r🅱 🅱t🅱e🅱s🅱t🅱 🅱w🅱i🅱l🅱l🅱 🅱b🅱e🅱g🅱i🅱n🅱 🅱i🅱n🅱 🅱3🅱0🅱 🅱s🅱e🅱c🅱o🅱n🅱d🅱s🅱.🅱 🅱L🅱i🅱n🅱e🅱 🅱u🅱p🅱 🅱a🅱t🅱 🅱t🅱h🅱e🅱 🅱s🅱t🅱a🅱r🅱t🅱."
// );

$potentialTextMemesNoEmoji = array(
    "This offends me as a vegan bitcoin trading hipster Native-American-Indo-Chinese hybrid alien agnostic-atheist German engineer who vapes fairtrade organic decaffeinated compressed and hydrated extra-protein soy breast milk on the regular and does Hindi Kama Sutra naked crossfit yoga 5 times per week. I'm also a neoliberal male feminist and identify myself as a pastafarian pansexual leftist Apache helicopter dog of mega multi alpha beta gamma delta omega combo god of hyper death who's in a polygamous polyamorous relationship to the chihuahua which helped me cross the border of Mexico because it hates Donald Trump. My dog also walks me to the park and doggy styles me, if you find that weird you're an ignorant arrogant homophobic gender-assuming globaphobic bloodthirsty gun-loving bestial sexist racist incestuous white-previlege misogynistic biased objectified raped privileged Nazi slave owner terrorist",

    "What the jiminy crickets did you just flaming say about me, you little bozo? I’ll have you know I graduated top of my class in the Cub Scouts, and I’ve been involved in numerous secret camping trips in Wyoming, and I have over 300 confirmed knots. I am trained in first aid and I’m the top bandager in the entire US Boy Scouts (of America). You are nothing to me but just another friendly face. I will clean your wounds for you with precision the likes of which has never been seen before on this annual trip, mark my words. You think you can get away with saying those shenanigans to me over the Internet? Think again, finkle. As we speak I am contacting my secret network of MSN friends across the USA and your IP is being traced right now so you better prepare for the seminars, man. The storm that wipes out the pathetic little thing you call your bake sale. You’re frigging done, kid. I can be anywhere, anytime, and I can tie knots in over seven hundred ways, and that’s just with my bare hands. Not only am I extensively trained in road safety, but I have access to the entire manual of the United States Boy Scouts (of America) and I will use it to its full extent to train your miserable butt on the facts of the continents, you little schmuck. If only you could have known what unholy retribution your little 'clever' comment was about to bring down upon you, maybe you would have held your silly tongue. But you couldn’t, you didn’t, and now you’re paying the price, you goshdarned sillyhead. I will throw leaves all over you and you will dance in them. You’re friggin done, kiddo.",

    "The FitnessGram™ Pacer Test is a multistage aerobic capacity test that progressively gets more difficult as it continues. The 20 meter pacer test will begin in 30 seconds. Line up at the start.",


    "What the darn-diddily-doodily did you just say about me, you little witcharooney? I’ll have you know I graduated top of my class at Springfield Bible College, and I’ve been involved in numerous secret mission trips in Capital City, and I have over 300 confirmed baptisms. I am trained in the Old Testament and I’m the top converter in the entire church mission group. You are nothing to me but just another heathen. I will cast your sins out with precision the likes of which has never been seen before in Heaven, mark my diddily-iddilly words. You think you can get away with saying that blasphemy to me over the Internet? Think again, friendarino. As we speak I am contacting my secret network of evangelists across Springfield and your IP is being traced by God right now so you better prepare for the storm, maggorino. The storm that wipes out the diddily little thing you call your life of sin. You’re going to Church, kiddily-widdily. Jesus can be anywhere, anytime, and he can turn you to the Gospel in over infinity ways, and that’s just with his bare hands. Not only am I extensively trained in preaching to nonbelievers, but I have access to the entire dang- diddily Bible collection of the Springfield Bible College and I will use it to its full extent to wipe your sins away off the face of the continent, you diddily-doo satan-worshipper. If only you could have known what holy retribution your little “clever” comment was about to bring down upon you from the Heavens, maybe you would have held your darn-diddily-fundgearoo tongue. But you couldn’t, you didn’t, and now you’re clean of all your sins, you widdillo-skiddily neighborino. I will sing hymns of praise all over you and you will drown in the love of Christ. You’re farn-foodily- flank-fiddily reborn, kiddo-diddily.",

    "This is so sad 😞 Alexa, play Despacito 2

ɴᴏᴡ ᴘʟᴀʏɪɴɢ:

Despacito 2 (Feat: Lil Pump, XXX Tentacion, MinecraftAwesomeParodies, Pink Guy, Death Grips, 6ix9ine, 2pac, Joji, Lil Peep, Lil Yatchy, Ameer Vann, Twenty One Pilots, Blank Banshee, Lil Xan, Kuwait Grips, Tyler The Creator, Jaden Smith, Morrinsoney, Kurt Cobain, Yung Caucasian, DJ Pajamas, Link Wrey, Ice T, Péricles, Lil Gay, Rihanna, Chris Brown, Beyoncé, Eazy E, Pink Floyd, Cal Chuchesta, Ice Cube, BROCKHAMPTON, Odd Future, David Bowie, Elon Musk, Politikz, Yung Thug, Foo Fighters, Billie Holiday, Nat King Cole, My Chemical Romance, Smash Mouth, DJ Khaled, Imagine Dragons, Bjork, The Notourious B.I.G, Mad Dogg, Garth Brooks, JPEG Mafia, Mozart, Car Seat Headrest, Sex Bob-Omb, Frank Jav Cee, Sandtimer, Skrillex, Ghostemane, Nikki Minaj, Kid Cudi, Metallica, John Lennon and The Plastic Yoko Band, Pogo, AC DC, 2 8 1 4, The Beatles, Rolling Stones, Swans, Alexander Hamilton, Jack Douglass, Talking Heads, Arctic Monkeys, Paramore, Iron Maiden, Panic! At The Disco, Nirvana, Avril Lavigne, All Time Low, AJR, Green Day, Deadmau5, Simple Plan, Vanilla Ice, Eminem, The Killers, Drake, Hannah Montana, Vacations, Frank Ocean, Radiohead, Marshmello, Rex Orange County, The Strokes, Kali Uchis, Cardi B, Fall Out Boy, Blink-182, Michael Jackson, Agepê, Jack Johnson, Ninja Sex Party, BTS, Floral Shoppe, Pusha T, Eric Clapton, Pitbull, Will.i.am, Black Eyed Peas, Beastie Boys, Petshop Boys, R.E.M, Tame Impala, Backstreet Boys, The Ink Spots, Kanye West, Dean Martin, Logic, Marty Robbins, KE$HA, G-Eazy, Weezer, Toto, Darude, Shawn Mendes, Maroon Five, Father John Misty, Ed Sheeran, Post Malone, American Hi-Fi, Dashboard Confession, Miley Cyrus, Bob Marley, Nardwuar, King Buzzo, Marvin Gaye, Childish Gambino, Wu Tang Clan, Yellowcard, Leon Noel, Simon and Garfunkel, Elvis Presley, Justin Bieber, Matty B, Dawn, Dawn, Dawn, Johnny Cash, Helen Forrest, Hank Thompson, Tina Fonda, Prince, Rick James, Frank Sinatra, Lil Uzi Vert, A$AP Mob, Home, The Smiths, Joy Division, Luis Fonsi, Daddy Yankee)
",
    "Excuse me, but I have played many a hentai game so I am no stranger to a naked female or how to pleasure one. The idea that a gamer of my intellect would not know how to deal with a mere female human without clothing is simply laughable. I have been though hundreds, nay, thousands of simulations which cover every permutation of events that could possibly happen with a female so I would absolutely know how to provide her pleasure given the opportunity. Don't underestimate the power of hentai, my friend",

    "So today in Spanish class, my teacher told us that we would be listening to a song in Spanish. Already, I began to tremble. I had a bad feeling about this. “Which one?” I ask shakily, not wanting to hear the answer. “Despacito” She responds. I begin to hyperventilate. My worst fears have been realized. I fade in and out of conciseness. I clamp my palms over my ears, but I know it’s futile. The song plays. I’m crying now, praying. God, Allah, Buddha please help me. I curl up on the floor. There’s nothing I can do now. And then it happens. The chorus plays. The girls in my class open their mouths. The screams of the damned, the shrieks of the tortured fill my ears and bounce around my skull. My eardrums rupture, blood leaking out. I try to scream, but no sound comes out. I can only sit there, violently shaking as it happens to me. After what seems like hours, it’s finally over. I try to move, but I cannot make myself. My brain shuts down as my vision fades to black. I muster the last of my energy, uttering the accursed word. Despacito",
    "As I sat in the Harvard Dean's office in front of the board of reviewers for my application, the Dean asks me 'Why should you be a good candidate for this school?' They seemed bored but I replied 'Well I was born a child prodigy, placed 1st in my state spelling bee for three consecutive years, I can speak eight different languages not counting Latin, play four different instruments, I skipped grades 4 through 6, and graduated my high school as valedictorian at the age of 14. I then worked as an intern at both Telsa, and NASA.' Suddenly the room burst into laughter and many of board instantly started scribbling down 'No' near the application check marks. The Dean says 'Sorry but you are just not the type we are looking for.' But then I said 'Excuse me but I wasn't finished... I watch Rick and Morty' The Dean looked at me like an idiot and said 'So....?' Then I replied with a smile 'And I understand all the references and subtle jokes' An audible gasp let out by the board was so loud the secretary had to come in. You could hear a pin drop and then suddenly all at once the entire board clicked their pens on the 'Approved Box' and I was instantly handed a diploma and now I'm teaching advanced physicals there. I guess you can say I'm pretty smart.",

    "Technically, pansexuality is the least gay sexuality

If you are straight, you like girls, who like guys, which means it's 25% gay

If you are gay, well that's 100% gay

Bisexuality is 50% gay, because you like both

Pansexuality however, is an attraction to all the genders, 56 are politically recognized, and only one is male, so Pansexuality only makes you 1.78% gay

This concludes my research, good day!"

);

function generateCopyPasta()
{
    global $potentialTextMemesNoEmoji;
  // $emoji_list = array('😁','😂','😃','😄','😅','😆','😉','😊','😋','😌','😍','😏','😒','😓','😔','😖','😘','😚','😜','😝','😞','😠','😡','😢','😣','😤','😥','😨','😩','😪','😫','😭','😰','😱','😲','😳','😵','😷','😸','😹','😺','😻','😼','😽','😾','😿','🙀','🙅','🙆','🙇','🙈','🙉','🙊','🙋','🙌','🙍','🙎','🙏','🚀','🚃','🚄','🚅','🚇','🚉','🚌','🚏','🚑','🚒','🚓','🚕','🚗','🚙','🚚','🚢','🚤','🚥','🚧','🚨','🚩','🚪','🚫','🚬','🚭','🚲','🚶','🚹','🚺','🚻','🚼','🚽','🚾','🛀','🅰','🅱','🅾','🅿','🆎','🆑','🆒','🆓','🆔','🆕','🆖','🆗','🆘','🆙','🆚','🈁','🈂','🈚','🈯','🈲','🈳','🈴','🈵','🈶','🈷','🈸','🈹','🈺','🉐','🉑','🀄','🃏','🌀','🌁','🌂','🌃','🌄','🌅','🌆','🌇','🌈','🌉','🌊','🌋','🌌','🌏','🌑','🌓','🌔','🌕','🌙','🌛','🌟','🌠','🌰','🌱','🌴','🌵','🌷','🌸','🌹','🌺','🌻','🌼','🌽','🌾','🌿','🍀','🍁','🍂','🍃','🍄','🍅','🍆','🍇','🍈','🍉','🍊','🍌','🍍','🍎','🍏','🍑','🍒','🍓','🍔','🍕','🍖','🍗','🍘','🍙','🍚','🍛','🍜','🍝','🍞','🍟','🍠','🍡','🍢','🍣','🍤','🍥','🍦','🍧','🍨','🍩','🍪','🍫','🍬','🍭','🍮','🍯','🍰','🍱','🍲','🍳','🍴','🍵','🍶','🍷','🍸','🍹','🍺','🍻','🎀','🎁','🎂','🎃','🎄','🎅','🎆','🎇','🎈','🎉','🎊','🎋','🎌','🎍','🎎','🎏','🎐','🎑','🎒','🎓','🎠','🎡','🎢','🎣','🎤','🎥','🎦','🎧','🎨','🎩','🎪','🎫','🎬','🎭','🎮','🎯','🎰','🎱','🎲','🎳','🎴','🎵','🎶','🎷','🎸','🎹','🎺','🎻','🎼','🎽','🎾','🎿','🏀','🏁','🏂','🏃','🏄','🏆','🏈','🏊','🏠','🏡','🏢','🏣','🏥','🏦','🏧','🏨','🏩','🏪','🏫','🏬','🏭','🏮','🏯','🏰','🐌','🐍','🐎','🐑','🐒','🐔','🐗','🐘','🐙','🐚','🐛','🐜','🐝','🐞','🐟','🐠','🐡','🐢','🐣','🐤','🐥','🐦','🐧','🐨','🐩','🐫','🐬','🐭','🐮','🐯','🐰','🐱','🐲','🐳','🐴','🐵','🐶','🐷','🐸','🐹','🐺','🐻','🐼','🐽','🐾','👀','👂','👃','👄','👅','👆','👇','👈','👉','👊','👋','👌','👍','👎','👏','👐','👑','👒','👓','👔','👕','👖','👗','👘','👙','👚','👛','👜','👝','👞','👟','👠','👡','👢','👣','👤','👦','👧','👨','👩','👪','👫','👮','👯','👰','👱','👲','👳','👴','👵','👶','👷','👸','👹','👺','👻','👼','👽','👾','👿','💀','💁','💂','💃','💄','💅','💆','💇','💈','💉','💊','💋','💌','💍','💎','💏','💐','💑','💒','💓','💔','💕','💖','💗','💘','💙','💚','💛','💜','💝','💞','💟','💠','💡','💢','💣','💤','💥','💦','💧','💨','💩','💪','💫','💬','💮','💯','💰','💱','💲','💳','💴','💵','💸','💹','💺','💻','💼','💽','💾','💿','📀','📁','📂','📃','📄','📅','📆','📇','📈','📉','📊','📋','📌','📍','📎','📏','📐','📑','📒','📓','📔','📕','📖','📗','📘','📙','📚','📛','📜','📝','📞','📟','📠','📡','📢','📣','📤','📥','📦','📧','📨','📩','📪','📫','📮','📰','📱','📲','📳','📴','📶','📷','📹','📺','📻','📼','🔃','🔊','🔋','🔌','🔍','🔎','🔏','🔐','🔑','🔒','🔓','🔔','🔖','🔗','🔘','🔙','🔚','🔛','🔜','🔝','🔞','🔟','🔠','🔡','🔢','🔣','🔤','🔥','🔦','🔧','🔨','🔩','🔪','🔫','🔮','🔯','🔰','🔱','🔲','🔳','🔴','🔵','🔶','🔷','🔸','🔹','🔺','🔻','🔼','🔽','🕐','🕑','🕒','🕓','🕔','🕕','🕖','🕗','🕘','🕙','🕚','🕛','🗻','🗼','🗽','🗾','🗿','😀','😇','😈','😎','😐','😑','😕','😗','😙','😛','😟','😦','😧','😬','😮','😯','😴','😶','🚁','🚂','🚆','🚈','🚊','🚍','🚎','🚐','🚔','🚖','🚘','🚛','🚜','🚝','🚞','🚟','🚠','🚡','🚣','🚦','🚮','🚯','🚰','🚱','🚳','🚴','🚵','🚷','🚸','🚿','🛁','🛂','🛃','🛄','🛅','🌍','🌎','🌐','🌒','🌖','🌗','🌘','🌚','🌜','🌝','🌞','🌲','🌳','🍋','🍐','🍼','🏇','🏉','🏤','🐀','🐁','🐂','🐃','🐄','🐅','🐆','🐇','🐈','🐉','🐊','🐋','🐏','🐐','🐓','🐕','🐖','🐪','👥','👬','👭','💭','💶','💷','📬','📭','📯','📵','🔀','🔁','🔂','🔄','🔅','🔆','🔇','🔉','🔕','🔬','🔭','🕜','🕝','🕞','🕟','🕠','🕡','🕢','🕣','🕤','🕥','🕦','🕧');
    $emoji_list = array('😂', '🖕', '💦🍌', '💦', '💩', '👉👌', '😖', '😣', '😩', '😫💦💦🙏🏿', '✊🏽✊🏽💦');

    $randomEmoji = $emoji_list[array_rand($emoji_list)];
    $randomCopypasta = $potentialTextMemesNoEmoji[array_rand($potentialTextMemesNoEmoji)];
  // return implode($randomEmoji, str_split($randomCopypasta));
    return str_replace(' ', $randomEmoji, $randomCopypasta);

}
require "/Applications/XAMPP/xamppfiles/htdocs/youtubeChannelsToCommentOn.php";

$commentsMade = file('commentsMade.txt', FILE_IGNORE_NEW_LINES);
/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ . '"');
}

require_once __DIR__ . '/vendor/autoload.php';

// session_start();
// session_unset();
// session_destroy();

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL);

// $redirect = "http://youtubebot.com/youtubeuploadbot/index.php";

$client->setRedirectUri($redirect);
$why = $client->refreshToken($refreshToken);
$client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
echo ("<pre>");
echo 'why';
print_r($why);
echo ("</pre>");

// Define an object that will be used to make all API requests.

$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes

$tokenSessionKey = 'token-' . $client->prepareScopes();

if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();

    // header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

echo ("<pre>");
echo 'access token';
print_r($client->getAccessToken());
echo ("</pre>");

foreach ($tempDownloadsMemes as $key => $value) {
    if (in_array($value, $memesAlreadyJudgedForLength)) {
        continue;
    }
    $currentVideoDuration = shell_exec("$ffmpeg -i /Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/tempDownloadedMemes/$value 2>&1 | grep Duration");
    $actualDuration = substr($currentVideoDuration, 11, 12);
    $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
    $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1] * 60 + $arrayWithHoursMinsAndSecs[0] * 3600;
    if ($thisVideoDurationInSeconds > 60) {
        echo ("<pre>");
        echo 'found a vidoe that is longenough';
        echo ("</pre>");
        copy("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/tempDownloadedMemes/" . $value, "./individualMemesToName/tempname_$value");
    }

    // this is why it wont put the same video in the dir even after i delete it

    file_put_contents("memesAlreadyJudgedForLength.txt", $value . "\n", FILE_APPEND);
}

$individualMemesToNameFolder = scandir('./individualMemesToName');
$arrayOfFileSizes = array();

foreach ($individualMemesToNameFolder as $key => $value) {
    echo ("<pre>");

    // echo $value;
    // echo '         this individual meme filesize: '.filesize('./individualMemesToName/'.$value)."\n";

    echo ("</pre>");
    if (in_array(filesize('./individualMemesToName/' . $value), $arrayOfFileSizes)) {
        unlink("./individualMemesToName/$value");
        echo 'found identical filesize';
    }

    array_push($arrayOfFileSizes, filesize('./individualMemesToName/' . $value));
    if (stripos($value, 'tempname') === false && stripos($value, 'mp4') !== false) {
        array_push($arrayOfIndividualVideosToUpload, $value);
    }
}

echo ("<pre>");
echo 'array of individual videos to upload: ';
print_r($arrayOfIndividualVideosToUpload);
echo ("</pre>");

// $individualMemesCreated = file("individualMemesCreated.txt", FILE_IGNORE_NEW_LINES);

$individualMemesCreated = scandir('./finalVids');
$arrayOfOutputVideoNames = array();

foreach ($arrayOfIndividualVideosToUpload as $key => $value) {
    rename("./individualMemesToName/$value", "./individualMemesToName/" . str_replace(" ", '@', str_replace("'", '£', $value)));

    // cant use substr_count because it will only get the first bit in quotes

    $videoFileName = str_replace(" ", '@', str_replace("'", '£', $value));
    if (in_array('finished_' . $videoFileName, $individualMemesCreated)) {
        echo 'meme already created ';
        continue;
    }

    // pound sign replaces quote, @ replaces spacestr_replace(" ",'@',str_replace("'", '£', $value))

    echo ("<pre>");
    echo 'video file name ';
    print_r($videoFileName);
    echo ("</pre>");
    $videoInfo = shell_exec("$ffmpeg -i './individualMemesToName/$videoFileName' 2>&1 ");
    $videoInfoArray = explode(",", $videoInfo);
    $dimensions = explode("x", $videoInfoArray[11]);
    $width = $dimensions[0];
    $height = explode(" ", $dimensions[1])[0];
    echo ("<pre>");
    echo 'width: ';
    echo $width . "\n";
    echo 'height: ';
    echo $height . "\n";
    echo ("</pre>");
    echo 'videoinfo: ';
    print_r($videoInfo);
    if ($width >= $height) {
        echo shell_exec("$ffmpeg -i ./individualMemesToName/$videoFileName -filter_complex 'setpts=PTS-STARTPTS,scale=1080:-1,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x2F2F2F,setsar=sar=1/1,fps=fps=30' -video_track_timescale 15360 ./resizedVideos/resized_$videoFileName 2>&1");
    } else {
        echo 'vertical video ';
        echo shell_exec("$ffmpeg -i ./individualMemesToName/$videoFileName -filter_complex 'setpts=PTS-STARTPTS,scale=-1:1080,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x2F2F2F,setsar=sar=1/1,fps=fps=30' -video_track_timescale 15360 ./resizedVideos/resized_$videoFileName 2>&1");
    }

    array_push($arrayOfOutputVideoNames, "resized_$videoFileName");

    // add background echo ("<pre>");

    echo ("<pre>");
    echo shell_exec("$ffmpeg -i background.mov -i ./resizedVideos/resized_$videoFileName -i memestreamwatermark.png -i subscribetext.png -i mslogo.png -i singlememetext.png -i memestream.png -y -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+450:((main_h-overlay_h)/2)+465[overlay2]; [overlay2][3:v]overlay=1495:(main_h-overlay_h)/2+200[overlay3];[overlay3][4:v]overlay=0:main_h-overlay_h[overlay4];[overlay4][5:v]overlay=5:50[overlay5];[overlay5][6:v]overlay=1530:30[output]' -map '[output]' -map 1:1 ./finalVidsNoIntroOrOutro/finished_$videoFileName 2>&1");
    echo ("</pre>");
    echo ("<pre>");
    echo shell_exec("$ffmpeg -i memestreamintro.mp4 -i  ./finalVidsNoIntroOrOutro/finished_$videoFileName -i memestreamoutro.mp4 -y -filter_complex 'concat=n=3:v=1:a=1[v][a]' -map '[v]' -map '[a]' ./finalVids/finished_$videoFileName 2>&1");
    echo ("</pre>");

    // and intro and outro

    file_put_contents("individualMemesCreated.txt", $videoFileName . "\n", FILE_APPEND);
    unlink("./finalVidsNoIntroOrOutro/finished_$videoFileName");
    unlink("./resizedVideos/resized_$videoFileName");
}

$timeOflastIndividualMemeMade = file("timeoflastuploadedindividualmeme.txt", FILE_IGNORE_NEW_LINES);
$timeOflastCompilationMemeMade = file("timeOflastCompilationMeme.txt", FILE_IGNORE_NEW_LINES);
$individualMemesUploaded = file("individualMemesUploaded.txt", FILE_IGNORE_NEW_LINES);

// i dont think there is any point in the individual memes uploaded txt but hey ho i set it up so lets just keep it ftb

$individualMemesCreated = scandir_only_wanted_files('./finalVids');
echo ("<pre>");
echo 'array size before:';
print_r(count($individualMemesCreated));
echo ("</pre>");

foreach ($individualMemesCreated as $key => $value) {
    if (in_array($value, $individualMemesUploaded)) {
        unset($individualMemesCreated[$key]);
    }
}


//this is actuall last type of video attempted to be uploaded just in case i run out of a certain type of video
$lastTypeOfVideoUploaded = end(file("lastTypeOfVideoUploaded.txt", FILE_IGNORE_NEW_LINES));
echo ("<pre>");
echo 'last type of video uploaded: ';
print_r($lastTypeOfVideoUploaded);
echo ("</pre>");

//basically what i should do from now on is make it reject any video trying to be posted if the last one was less than $timeAllowedBetweenVideosFullStop ago and then just keep track of
// what type of video was last posted so i cant alternate - make sure to ensure that there are enough of one type of video - actuall what i can do is just put the last type of video in a txt (obvs when enough time has passed) regardless of whether it actuall posted successfully - ie it might not have coz it doest exist.
//if (end($timeOflastCompilationMemeMade) + $timeAllowedBetweenIndividualMemes / 2 < time() && end($timeOflastIndividualMemeMade) + $timeAllowedBetweenIndividualMemes < time())
if ($lastTypeOfVideoUploaded == 'compilation' && $shouldTryToPostIndividualMemes) {
    echo "<p> <font color=blue font face='arial' size='10pt'>trying to upload individual meme </font> </p>";
    $typeOfVideoTryingToBeUploaded = 'individual';
    $finalVidsDir = "./finalVids";
    $videoPath = "$finalVidsDir/" . end($individualMemesCreated);
    echo ("<pre>");
    echo 'video path: ';
    print_r($videoPath);
    echo ("</pre>");
    $tryingToPostIndividualMeme = true;
    $titleOfIndividualFile = substr(str_replace("finished_", "", str_replace('£', "'", str_replace('@', ' ', end($individualMemesCreated)))), 0, -4);
    echo "title of individual file: ", $titleOfIndividualFile;
    if ($insaneMode) {
        $shouldStopMeme = true;
    }
} elseif ($lastTypeOfVideoUploaded == 'individual' || !$shouldTryToPostIndividualMemes) {
    echo "<p> <font color=blue font face='arial' size='10pt'>trying to upload compilation </font> </p>";
    $typeOfVideoTryingToBeUploaded = 'compilation';
    $videoPath = "$finalVidsDir/" . end($memesToUpload);
    $tryingToPostIndividualMeme = false;
}

$memeItsUploading = substr(end($memesToUpload), 0, -4);
echo ("<pre>");
echo 'meme its uploading (if trying compilation): ';
print_r($memeItsUploading);
echo ("</pre>");
$listOfMemeIDsInVideos = file("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/textFilesWithMemeIDs/$memeItsUploading.txt", FILE_IGNORE_NEW_LINES);
echo ("<pre>");
echo 'list of meme ids in video ';
print_r($listOfMemeIDsInVideos);
echo ("</pre>");
$individualMemeTags = array();
$explodeIndividualMemeTitle = explode(' ', $titleOfIndividualFile);
echo 'title: ';
print_r(array_reverse($explodeIndividualMemeTitle));

foreach (array_reverse($explodeIndividualMemeTitle) as $key => $value) {
    if (strlen($value) > 2) {
        array_push($individualMemeTags, $value);
    }
}
    //you are only allowed 25 keywords. ffs this was the problem
$videoTags = array(
    "dank memes compilation",
    "memes $todaysDate[month] $todaysDate[year]",
    "funny memes",
    "dank memes $todaysDate[month] $todaysDate[year]",
    "hilarious dank memes",
    "funny vines",
    "best vines $todaysDate[month] $todaysDate[year]",
    "best memes",
    "try not to laugh",
    "try not to laugh challenge",
    "ultimate succ dank memes",
    "pewdiepie",
    "meme compilation",
    "dank memes edition",
    "funny memes",
    "you laugh you lose",
    "dank vines",
    "meme stream",
    "best try not to laugh $todaysDate[year]",
    "dank memes edition $todaysDate[year]",
    "memes",
    "succ",
    "ylyl",
    "best of $todaysDate[month] $todaysDate[year]",
    "$todaysDate[year] best memes",
    "I bet you will laugh"


);

echo ("<pre>");
echo " video keywords  ";
print_r($videoTags);
echo ("</pre>");
$tagsLength = 0;
$newTagsLength = 0;


foreach ($videoTags as $key => $value) {
    $tagsLength += strlen($value);
}

foreach ($individualMemeTags as $key => $value) {
    $tagBeingAddedLength = strlen($value);

        // array_unshift($videoTags,$value );
}

$newTagsLength = $tagsLength;
while ($newTagsLength > 500) {
    unset($videoTags[count($videoTags) - 1]);
    $newTagsLength -= strlen($videoTags[count($videoTags) - 1]);
}

$newTagsLength = 0;
foreach ($videoTags as $key => $value) {
    $newTagsLength += strlen($value);
}


echo 'tags length: ' . $tagsLength;
echo 'new tags length: ' . $newTagsLength;
echo ("<pre>");
echo 'tags: ';
print_r($videoTags);
echo ("</pre>");
echo ("<pre>");

echo ("</pre>");

// REPLACE this value with the path to the file you are uploading.
// $videoPath  = 'meme.mp4';

echo ("<pre>");
echo 'videopath: ';
print_r($videoPath);
echo ("</pre>");

echo ("<pre>");
echo 'memes to upload: ';
print_r($memesToUpload);
echo ("</pre>");

if (empty($memesToUpload) && !$tryingToPostIndividualMeme) {
    echo 'not actually duplicate but the memes to upload is empty lol';
    $shouldStopMeme = true;
}
    //do this ^^ for individual memes too - acc no its not the same
echo ("<pre>");
echo 'videopath end: ';
echo substr($videoPath, -4);

echo ("</pre>");
if (substr($videoPath, -4) != '.mp4') {
    echo 'no mp4 in videopath name, stopping the posting of it';
    $shouldStopMeme = true;
}


if (!$tryingToPostIndividualMeme) {
    $description = "Thanks for watching this hilarious dank memes compilation! Make sure you subscribe for more dank and edgy memes and like this video if you thought the memes were funny! Please feel free to contact us!\n


Play some dank meme games and other games on your mobile phone:
https://goo.gl/GH9e3l";
} else {
    $description = "Thanks for watching $titleOfIndividualFile! Make sure you subscribe for more dank and edgy memes and like this video if you thought the meme was funny! Please feel free to contact us!\n


    Play some dank meme games and other games on your mobile phone:
    https://goo.gl/GH9e3l";
}

$autoCommentMessage = "Thank you all for watching! - I really appreciate it and hoped you enjoyed! If you could take just $randomNumber seconds out of your day to like the video and subscribe that would be LIT and I will <3 you forever.

Also make sure to check out all the links which can all be found in lé description.

I do apologise for duplicate memes and other glitches, you must understand that this is a bot that auto creates the memes, and while it isn't perfect right now, it is constantly being improved. PLEASE Tell me which videos are bad and I will remove the pages (gimme a time at which the video starts playing) :p

It would also be a massive help if you could provide me with some Facebook video meme pages so I can add to the bot, or even from another website or Youtube channel - that would be greatly appreciated!
";

if (!$tryingToPostIndividualMeme) {
    $description = $description . "\n\nHere are links to the memes we used in this compilation:\n\n";
    foreach ($listOfMemeIDsInVideos as $key => $value) {
        $whatMemeIsThis = $key + 1;
        $description = $description . $whatMemeIsThis . ": https://facebook.com/" . $listOfMemeIDsInVideos[$key] . "\n";
    }
}

$volume = 1;
$memesAlreadyUploaded = file('/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/memesactuallyuploaded.txt', FILE_IGNORE_NEW_LINES);


//this should work for both types of video
foreach ($memesAlreadyUploaded as $keyuploaded => $valueuploaded) {
    if ($valueuploaded == $videoPath) {
        echo ("<pre>");
        echo " should stop meme because already uploaded  ";
        echo ("</pre>");
        $shouldStopMeme = true;
    }
}

echo ("<pre>");
echo 'should stop meme: ';
print_r($shouldStopMeme);
echo ("</pre>");

// ----------------------------------------------------------------------------------------------------
// Check to ensure that the access token was successfully acquired.


if ($client->getAccessToken()) {
    $htmlBody = '';

    function addPropertyToResource(&$ref, $property, $value)
    {
        $keys = explode(".", $property);
        $is_array = false;
        foreach ($keys as $key) {
            // Convert a name like "snippet.tags[]" to "snippet.tags" and
            // set a boolean variable to handle the value like an array.
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $is_array = true;
            }
            $ref = &$ref[$key];
        }

        // Set the property value. Make sure array values are handled properly.
        if ($is_array && $value) {
            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($is_array) {
            $ref = array();
        } else {
            $ref = $value;
        }
    }
    function createResource($properties)
    {
        $resource = array();
        foreach ($properties as $prop => $value) {
            if ($value) {
                addPropertyToResource($resource, $prop, $value);
            }
        }
        return $resource;
    }

    // put the if meme already upldoade thing here
    function videosUpdate($service, $properties, $part, $params)
    {
        $params = array_filter($params);
        $propertyObject = createResource($properties); // See full sample for function
        $resource = new Google_Service_YouTube_Video($propertyObject);
        $response = $service->videos->update($part, $resource, $params);
        echo 'response:';
        print_r($response);
    }
    try {
        $arrayOfVideosEligibleToCommentOn = array();
        foreach ($arrayOfChannelsToCommentOn as $key => $value) {
            $searchResponse = $youtube->search->listSearch('id,snippet', array(
                'type' => 'video',
                'part' => 'snippet',
                'channelId' => $value,
                'order' => 'date',
                'maxResults' => '1'
            ));
            //print_r($searchResponse);
            if (strtotime($searchResponse['items'][0]['snippet']['publishedAt']) > time() - $refreshRate) {
                // add video id and channel name to this array
                array_push($arrayOfVideosEligibleToCommentOn, array(
                    $searchResponse['items'][0]['snippet']['channelTitle'],
                    $searchResponse['items'][0]['id']['videoId'],
                    $searchResponse['items'][0]['snippet']['title']
                ));
            }

            if ($value == 'UCVb09t28gKnNt6QAHW96Vhw') {
                // turn this off when not testing:
                // here is the link to the test video https://www.youtube.com/watch?v=j_SLaNRf_xo
                array_push($arrayOfVideosEligibleToCommentOn, array(
                    $searchResponse['items'][0]['snippet']['channelTitle'],
                    $searchResponse['items'][0]['id']['videoId'],
                    $searchResponse['items'][0]['snippet']['title']
                ));
            }
        }


        if ($enablePosting) {
            // $arrayOfVideosEligibleToCommentOn[0] = 'DcIWwMNcU54';

            foreach ($arrayOfVideosEligibleToCommentOn as $key => $value) {
                echo ("<pre>");
                echo 'key: ';
                print_r($key);
                print_r($value);
                echo ("</pre>");

                // $commentString = $potentialFirstPhrase[array_rand($potentialFirstPhrase)].", ".$value[0].", ".$potentialSecondPhrase[array_rand($potentialSecondPhrase)]." ".$potentialThirdPhrase[array_rand($potentialThirdPhrase)].", and ".$potentialFourthPhrase[array_rand($potentialFourthPhrase)].". ".$potentialFifthPhrase[array_rand($potentialFifthPhrase)].". ".$potentialSixthPhrase[array_rand($potentialSixthPhrase)]."! Thanks!";
                $videoTitle = $value[2];
                $retardedTitle = preg_replace('/(\w)(.)?/e', "strtolower('$1').strtoupper('$2')", $videoTitle);
                $commentString = generateCopyPasta() . ". \n\ns u b s c r i b e  i f  y o u  w a n t  m e m e s.";
                //$commentString = $retardedTitle . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\ni need subs lol";
                echo ("<pre>");
                // echo 'this is the sentence of the comment: ';
                // print_r($commentString);
                echo ("</pre>");
                if (in_array($value[1], $commentsMade)) {
                    continue;
                }
                if (!$enableCommentingShoutout) {
                    break;
                }

                // Create a comment snippet with text.

                $commentSnippet = new Google_Service_YouTube_CommentSnippet();
                $commentSnippet->setTextOriginal($commentString);

                // $commentSnippet->setTextOriginal("This is a nice video. ﻿". (mt_rand(400,800)/100));
                // Create a top-level comment with snippet.

                $topLevelComment = new Google_Service_YouTube_Comment();
                $commentThread = new Google_Service_YouTube_CommentThread();
                $commentThreadSnippet = new Google_Service_YouTube_CommentThreadSnippet();
                $topLevelComment->setSnippet($commentSnippet);
                $commentThread->setSnippet($commentThreadSnippet);
                $commentThreadSnippet->setTopLevelComment($topLevelComment);

                // Insert video comment

                $commentThreadSnippet->setVideoId($value[1]);

                // Call the YouTube Data API's commentThreads.insert method to create a comment.

                $videoCommentInsertResponse = $youtube->commentThreads->insert('snippet', $commentThread);
                file_put_contents('commentsMade.txt', $videoCommentInsertResponse['snippet']['videoId'] . "\n", FILE_APPEND);
                echo ("<pre>");



                //  file_put_contents('likelySpam.txt',"moderation status(vid id, mod status): ".$videoCommentInsertResponse['snippet']['videoId']."  ,  ".$videoCommentInsertResponse['snippet']['topLevelComment']["snippet"]["moderationStatus"]."\n",FILE_APPEND );

                file_put_contents('likelySpam.txt', "moderation status(channel id, vid id, time,  mod status): " . $videoCommentInsertResponse['snippet']['channelId'] . "  ,   " . $videoCommentInsertResponse['snippet']['videoId'] . "  ,  " . date('d H:i:s') . "  ,  " . $videoCommentInsertResponse['snippet']['topLevelComment']["snippet"]["moderationStatus"] . "\n", FILE_APPEND);
            }
        }

        // Call the channels.list method to retrieve information about the
        // currently authenticated user's channel.

        $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
            'mine' => 'true',
        ));
        $htmlBody = '';
        foreach ($channelsResponse['items'] as $channel) {

            // Extract the unique playlist ID that identifies the list of videos
            // uploaded to the channel, and then call the playlistItems.list method
            // to retrieve that list.

            $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];
            $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
                'playlistId' => $uploadsListId,
                'maxResults' => 50
            ));
            $arrayOfVideos = (array)$playlistItemsResponse;

            // $jsonOfVideos = json_encode($playlistItemsResponse);
            // $arrayOfVideos = json_decode($jsonOfVideos, true);

            echo ("<pre>");
            // echo 'my videos: ';
            // print_r($playlistItemsResponse);
            echo ("</pre>");
            $latestVideoTimestamp = 0;
            $latestCompilationTimestamp = 0;
            echo ("<pre>");
            echo 'current timestamp: ';
            print_r(time());
            echo ("</pre>");



            foreach ($playlistItemsResponse['items'] as $keyVideo => $valueVideo) {
                if ($shouldRenameVideosToNewTodaysDate) {
                    echo 'should rename video is on';
                    $title = (string)$valueVideo['snippet']['title'];
                    echo "video title: " . $title;


                    echo 'string pos ' . strpos("YLYL", $title);

                    if (strpos($title, "YLYL") !== false && strpos($title, "$todaysDate[month] $todaysDate[mday]") === false) {
                        echo 'about to rename video';
                        $indexOfVideoTitle = end(explode("#", $valueVideo['snippet']['title']));
                        videosUpdate(
                            $youtube,
                            array(
                                'id' => $valueVideo['snippet']['resourceId']['videoId'],
                                'snippet.title' => "BEST MEMES OF $todaysMonthCaps $todaysYearCaps YLYL COMPILATION #$indexOfVideoTitle",
                                'snippet.categoryId' => '23',

                            ),
                            'snippet,status',
                            array()
                        );
                    }
                }
                //greater than 0? it should always be greater than 0...ah but this just checks that there even is an array entry for publishedAt ...clevel
                if (strtotime($valueVideo['snippet']['publishedAt']) > $latestVideoTimestamp) {//&& stripos($valueVideo['snippet']['title'], "Compilation") !== false)
                    // latestvideotimestapm is actually only for the latest compilation, it ignores other types of vids
          //not any more its not - my system is fucked i need changes - it accounts for all types of video
                    $latestVideoTimestamp = strtotime($valueVideo['snippet']['publishedAt']);
                }

                if (strtotime($valueVideo['snippet']['publishedAt']) > $latestCompilationTimestamp && stripos($valueVideo['snippet']['title'], "YLYL") !== false) {
                    $latestCompilationTimestamp = strtotime($valueVideo['snippet']['publishedAt']);
                }

                // getting comments and checking if i commented on it is not a good idea as max results max is 100...i guess we'll just use ye good ol txt storage method instead
                // $videoComments = $youtube->commentThreads->listCommentThreads('snippet', array(
                //             'videoId' => $valueVideo['snippet']['resourceId']['videoId'],
                //             'textFormat' => 'plainText',
                //         ));
                $videosCommentedOn = file("videosCommentedOn.txt", FILE_IGNORE_NEW_LINES);
                $stopCommentBeingMade = false;
                foreach ($videosCommentedOn as $keyVidIDs => $valueVidIDs) {
                    if ($valueVideo['snippet']['resourceId']['videoId'] == $valueVidIDs) {
                        $stopCommentBeingMade = true;
                    }
                }

                if (!$stopCommentBeingMade) {

                    // Create a comment snippet with text.

                    $commentSnippet = new Google_Service_YouTube_CommentSnippet();
                    $commentSnippet->setTextOriginal($autoCommentMessage);

                    // Create a top-level comment with snippet.

                    $topLevelComment = new Google_Service_YouTube_Comment();
                    $commentThread = new Google_Service_YouTube_CommentThread();
                    $commentThreadSnippet = new Google_Service_YouTube_CommentThreadSnippet();
                    $topLevelComment->setSnippet($commentSnippet);
                    $commentThread->setSnippet($commentThreadSnippet);
                    $commentThreadSnippet->setTopLevelComment($topLevelComment);

                    // Insert video comment

                    $commentThreadSnippet->setVideoId($valueVideo['snippet']['resourceId']['videoId']);

                    // Call the YouTube Data API's commentThreads.insert method to create a comment.

                    $videoCommentInsertResponse = $youtube->commentThreads->insert('snippet', $commentThread);
                    // echo ("<pre>");
                    // echo 'comment being made: ';
                    // print_r($videoCommentInsertResponse);
                    // echo ("</pre>");
                    file_put_contents('videosCommentedOn.txt', $videoCommentInsertResponse['snippet']['videoId'] . "\n", FILE_APPEND);
                    // echo ("<pre>");
                    // echo 'video comments: ';
                    // print_r($videoComments);
                    // echo ("</pre>");
                }
            }

            echo ("<pre>");
            echo 'most recent video: ';
            print_r($latestVideoTimestamp);
            echo ("</pre>");
            if ($latestVideoTimestamp + $timeAllowedBetweenVideosFullStop > time()) {
                $shouldStopMeme = true;
                echo 'not enough time has passed';
                //this $stopBecauseOfTime is just for testing - i dont use it anywhere
                $stopBecauseOfTime = true;
            } else {
                file_put_contents("lastTypeOfVideoUploaded.txt", $typeOfVideoTryingToBeUploaded . "\n", FILE_APPEND);

                $stopBecauseOfTime = false;
            }

            foreach ($playlistItemsResponse['items'] as $keyVideo => $valueVideo) {
                //i changed the identifier of meme compilations from 'compilation' to # because the new name incorporates # but not the word compilation
                //this wont work? if the previous video was individual then it won't detect it as a comp meme
                if (strtotime($valueVideo['snippet']['publishedAt']) == $latestCompilationTimestamp && stripos($valueVideo['snippet']['title'], "#") !== false) {
                    echo ' the previous compilation was called: ' . $valueVideo['snippet']['title'];
                    $titleArray = explode(" ", $valueVideo['snippet']['title']);
                    $previousVolume = substr(end($titleArray), 1);
                    $volume = $previousVolume + 1;
                }
            }

            $htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
            foreach ($playlistItemsResponse['items'] as $playlistItem) {
                $htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'], $playlistItem['snippet']['resourceId']['videoId']);
            }

            $htmlBody .= '</ul>';
        }

        //if ($tryingToPostIndividualMeme) $shouldStopMeme = false;
        if ($testingMode) {
            $shouldStopMeme = false;
        }

        if (!$shouldStopMeme) {

            // Create a snippet with title, description, tags and category ID
            // Create an asset resource and set its snippet metadata and type.
            // This example sets the video's title, description, keyword tags, and
            // video category.

            $snippet = new Google_Service_YouTube_VideoSnippet();
            if ($tryingToPostIndividualMeme) {
                $snippet->setTitle($titleOfIndividualFile);
            } elseif ($testingMode) {
                $snippet->setTitle("testing");
            } else {
                //$snippet->setTitle("HILARIOUS Dank Memes Compilation #$volume");
                $title = "BEST MEMES OF $todaysMonthCaps $todaysYearCaps YLYL COMPILATION #$volume";
                $snippet->setTitle("$title");
                echo ("<pre>");
                echo "  title: ";
                print_r($title);
                echo ("</pre>");
            }

            $snippet->setDescription($description);
            $snippet->setTags($videoTags);

            // Numeric video category. See
            // https://developers.google.com/youtube/v3/docs/videoCategories/list

            $snippet->setCategoryId("23");

            // Set the video's status to "public". Valid statuses are "public",
            // "private" and "unlisted".

            $status = new Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = "public";

            // Associate the snippet and status objects with a new video resource.

            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            // Specify the size of each chunk of data, in bytes. Set a higher value for
            // reliable connection as fewer chunks lead to faster uploads. Set a lower
            // value for better recovery on less reliable connections.

            $chunkSizeBytes = 1 * 1024 * 1024;

            // Setting the defer flag to true tells the client to return a request which can be called
            // with ->execute(); instead of making the API call immediately.

            $client->setDefer(true);

            // Create a request for the API's videos.insert method to create and upload the video.

            $insertRequest = $youtube->videos->insert("status,snippet", $video);

            // Create a MediaFileUpload object for resumable uploads.

            $media = new Google_Http_MediaFileUpload($client, $insertRequest, 'video/*', null, true, $chunkSizeBytes);
            $media->setFileSize(filesize($videoPath));

            // Read the media file and upload it chunk by chunk.

            $status = false;
            $handle = fopen($videoPath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            echo ("<pre>");
            echo 'uploaded video';
            print_r($status['id']);
            echo ("</pre>");
            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false

            $client->setDefer(false);
            if ($tryingToPostIndividualMeme || $testingMode) {

                // REPLACE this value with the video ID of the video being updated.

                $videoId = $status['id'];

                // $videoPathForThumbnail = "/Applications/XAMPP/xamppfiles/htdocs/youtubeuploadbot/individualMemesToName/". substr(end($individualMemesCreated),9);

                $videoPathForThumbnail = "/Applications/XAMPP/xamppfiles/htdocs/youtubeuploadbot/individualMemesToName/" . str_replace(" ", '@', str_replace("'", '£', $status['snippet']['title'])) . ".mp4";
                echo ("<pre>");
                print_r($videoPathForThumbnail);
                echo ("</pre>");
                $currentVideoDuration = shell_exec("$ffmpeg -i $videoPathForThumbnail 2>&1 | grep Duration");
                $actualDuration = substr($currentVideoDuration, 11, 12);
                $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
                $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1] * 60 + $arrayWithHoursMinsAndSecs[0] * 3600;
                $pointToUseInVideoDuration = $thisVideoDurationInSeconds * $fractionOfWayThroughVideoToGetThumbnail;
                echo 'getting thumbnail from this part of this video: ' . $pointToUseInVideoDuration;
                $modifiedPointToUse = gmdate("H:i:s", $pointToUseInVideoDuration);
                echo shell_exec("$ffmpeg -i $videoPathForThumbnail -y -ss $modifiedPointToUse -vframes 1 thumbnail.png 2>&1");
                $imageSize = getimagesize('thumbnail.png');
                echo ("<pre>");
                echo ' image size: ';
                print_r($imageSize);
                echo ("</pre>");

                // remember, in order to get the centre, simply set the positions as the crop amount/2

                if ($imageSize[1] < $imageSize[0] * (9 / 16)) {
                    imagepng(imagecreatefrompng('thumbnail.png'), "cropped_thumbnail.png");
                } else {
                    $croppedImage = imagecrop(imagecreatefrompng('thumbnail.png'), ['x' => 0, 'y' => ($imageSize[1] - $imageSize[0] * (9 / 16)) / 2, 'width' => $imageSize[0], 'height' => $imageSize[0] * (9 / 16)]);
                    imagepng($croppedImage, "cropped_thumbnail.png");
                }

                // REPLACE this value with the path to the image file you are uploading.

                $imagePath = "cropped_thumbnail.png";

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.

                $chunkSizeBytes = 1 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.

                $client->setDefer(true);

                // Create a request for the API's thumbnails.set method to upload the image and associate
                // it with the appropriate video.

                $setRequest = $youtube->thumbnails->set($videoId);

                // Create a MediaFileUpload object for resumable uploads.

                $media = new Google_Http_MediaFileUpload($client, $setRequest, 'image/png', null, true, $chunkSizeBytes);
                $media->setFileSize(filesize($imagePath));

                // Read the media file and upload it chunk by chunk.

                $status = false;
                $handle = fopen($imagePath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false

                $client->setDefer(false);
            }
            //get our own thumbnail for compilations since they repeat now due to yt algo
            if (!$tryingToPostIndividualMeme) {

                    // REPLACE this value with the video ID of the video being updated.

                $videoId = $status['id'];
                //we should try to find a video in the compilation that has 1080p dimensions then if not take the screenshot of the whole fram.
                //actually nah, just crop a 1080 thing from the center of the video.

                $videoPathForThumbnail = $videoPath;
                echo ("<pre>");
                print_r($videoPathForThumbnail);
                echo ("</pre>");
                $currentVideoDuration = shell_exec("$ffmpeg -i $videoPathForThumbnail 2>&1 | grep Duration");
                $actualDuration = substr($currentVideoDuration, 11, 12);
                $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
                $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1] * 60 + $arrayWithHoursMinsAndSecs[0] * 3600;
                $pointToUseInVideoDuration = $thisVideoDurationInSeconds * $fractionOfWayThroughVideoToGetThumbnail;
                echo 'point of this video: ' . $pointToUseInVideoDuration;
                $modifiedPointToUse = gmdate("H:i:s", $pointToUseInVideoDuration);
                echo shell_exec("$ffmpeg -i $videoPathForThumbnail -y -ss $modifiedPointToUse -vf 'crop=1080:608' -vframes 1 thumbnail.png 2>&1");
                $imageSize = getimagesize('thumbnail.png');
                echo ("<pre>");
                echo ' image size: ';
                print_r($imageSize);
                echo ("</pre>");

                // remember, in order to get the centre, simply set the positions as the crop amount/2

                if ($imageSize[1] < $imageSize[0] * (9 / 16)) {
                    imagepng(imagecreatefrompng('thumbnail.png'), "cropped_thumbnail.png");
                } else {
                    $croppedImage = imagecrop(imagecreatefrompng('thumbnail.png'), ['x' => 0, 'y' => ($imageSize[1] - $imageSize[0] * (9 / 16)) / 2, 'width' => $imageSize[0], 'height' => $imageSize[0] * (9 / 16)]);
                    imagepng($croppedImage, "cropped_thumbnail.png");
                }

                // REPLACE this value with the path to the image file you are uploading.

                $imagePath = "cropped_thumbnail.png";

                // Specify the size of each chunk of data, in bytes. Set a higher value for
                // reliable connection as fewer chunks lead to faster uploads. Set a lower
                // value for better recovery on less reliable connections.

                $chunkSizeBytes = 1 * 1024 * 1024;

                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.

                $client->setDefer(true);

                // Create a request for the API's thumbnails.set method to upload the image and associate
                // it with the appropriate video.

                $setRequest = $youtube->thumbnails->set($videoId);

                // Create a MediaFileUpload object for resumable uploads.

                $media = new Google_Http_MediaFileUpload($client, $setRequest, 'image/png', null, true, $chunkSizeBytes);
                $media->setFileSize(filesize($imagePath));

                // Read the media file and upload it chunk by chunk.

                $status = false;
                $handle = fopen($imagePath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                fclose($handle);

                // If you want to make other calls after the file upload, set setDefer back to false

                $client->setDefer(false);
            }

            if ($tryingToPostIndividualMeme && !$testingMode) {
                file_put_contents("timeoflastuploadedindividualmeme.txt", time() . "\n", FILE_APPEND);
                file_put_contents("individualMemesUploaded.txt", end($individualMemesCreated) . "\n", FILE_APPEND);
            }

            if (!$tryingToPostIndividualMeme && !$testingMode) {
                file_put_contents("timeOflastCompilationMeme.txt", time() . "\n", FILE_APPEND);
                unlink($videoPath);
            }

            if (!$testingMode) {
                file_put_contents('/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/memesactuallyuploaded.txt', $videoPath . "\n", FILE_APPEND);
                // ihave no idea wtf this block does - oh, it is meant to remove the most recent meme in the memes to upload txt. the problem was it had file append when it should not have

                file_put_contents("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/$nameOfMemesToUploadFile", "");
                echo ("<pre>");
                echo " replacing $nameOfMemesToUploadFile  ";
                echo ("</pre>");
                $fileJustUploaded = end(explode("/", $videoPath));
                echo '  file just uploaded ' . $fileJustUploaded;
                foreach ($memesToUpload as $keytoupload => $valuetoupload) {
                    if ($valuetoupload != $fileJustUploaded) {
                        file_put_contents("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/$nameOfMemesToUploadFile", $valuetoupload . "\n", FILE_APPEND);
                    } else {
                        echo 'deleting meme from memes to upload as it is trying to upload rn';
                    }
                }

                if (!$tryingToPostIndividualMeme) {
                    foreach ($listOfMemeIDsInVideos as $key => $value) {
                        file_put_contents('memesFromTempFolderActuallyUploadedInCompilation.txt', $value . "\n", FILE_APPEND);
                    }
                }
            }

            $htmlBody .= "<h3>Video Uploaded</h3><ul>";
            $htmlBody .= sprintf('<li>%s (%s)</li>', $status['snippet']['title'], $status['id']);
            $htmlBody .= '</ul>';
        } else {
            if ($stopBecauseOfTime) {
                echo '  stopped because not enough time has passed  ';
            } else {
                //we dont know this is true necessarily
                //echo '  stopped duplicate  ';
            }
        }
    } catch (Google_Service_Exception $e) {
        $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    }

    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {

    // If the user hasn't authorized the app, initiate the OAuth flow

    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $authUrl = $client->createAuthUrl();
    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
echo ("</pre>");

?>

<!doctype html>
<html>
<head>
<title>Video Uploaded</title>
</head>
<body>
  <?php echo $htmlBody
    ?>
  <meta http-equiv="refresh" content="1000;url=http://youtubebot.com/youtubeuploadbotphp">

</body>
</html>
