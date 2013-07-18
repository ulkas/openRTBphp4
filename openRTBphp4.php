<?php
/**
 * openRTB v2.1 implementation for php4
 *
 * @method JSON
 * @todo implement video, app objects.
 * @author ulkas
 * @version 0.2
 **/

/**
 *
 * few neccessary static functions.
 *
 * not a part of openRTB documentation
 */
class openRTB_Staticfunctions{
	/**
	 *
	 * might help when debuging, or might not.
	 */
	function isDebug(){
		if (isset($_GET['debug'])) return $_GET['debug'];
		if (isset($_POST['debug']))	return $_POST['debug'];
		return false;
	}
	/**
	 *
	 * every openRTB class has a param "ext".
	 *
	 * in this function we can define rules upon which the specific extension class could be picked.
	 * @todo define rules when neccessary
	 * @param mixed $params
	 */
	function getExtClass($params=array()){
		return new stdClass();
	}
}

class openRTB {
	var $debug	=	false;
	var $method	=	"POST";
	var $responseOK	=	200;
	var $responseNO	=	204;
	var $headers	=	array('Connection: Keep-Alive',
							  'Content-Type: application/json',
							  'x-openrtb-version: 2.1');
}
/**#@+
 * bid requests objects
 */

/**
 *
 The top-level bid request object contains a globally unique bid request or auction ID.  This “id”
 attribute is required as is at least one “imp” (i.e., impression) object.  Other attributes are
 optional since an exchange may establish default values.
 The Default column dictates how optional parameters should be interpreted if explicit values
 are not provided.
 *
 */
class openRTBrequest {
	/**
	 * @var object
	 */
	var $imp=array();var $site=null;var $device=null;var $user=null;var $ext=null;
	/**
	 * @var mixed
	 */
	var $id='';var $at=2;var $tmax;var $wseat=array();var $allimps=0;var $cur=array();var $bcat=array();var $badv=array();

	/**
	 * @param object $mixed
	 */
	function openRTBrequest($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//Unique ID of the bid request, provided by the exchange.
		} else return null;
		if(isset($mixed->imp) && is_array($mixed->imp)){
			foreach ($mixed->imp as $value) {
				if (is_object($value))
				$this->imp[]=new openRTB_impression($value);				//Array of impression objects. Multiple impression auctions may be specified in a single bid request.  At least one impression is required for a valid bid request.
			}
		} else return null;
		if(isset($mixed->site) && is_object($mixed->site)){
			$this->site=new openRTB_site($mixed->site);						//See Site Object
		}
		if(isset($mixed->device) && is_object($mixed->device)){
			$this->device=new openRTB_device($mixed->device);				//See Device Object
		}
		if(isset($mixed->user) && is_object($mixed->user)){
			$this->user=new openRTB_user($mixed->user);						//See User Object
		}
		if(isset($mixed->at)){
			$this->at=intval($mixed->at);									//Auction Type. If “1”, then first price auction.  If “2”, then second price auction.  Additional auction types can be defined as per the exchange’s business rules.  Exchange specific rules should be numbered over 500.
		}
		if(isset($mixed->tmax)){
			$this->tmax=intval($mixed->tmax);								//Maximum amount of time in milliseconds to submit a bid (e.g., 120 means the bidder has 120ms to submit a bid before the auction is complete).  If this value never changes across an exchange, then the exchange can supply this information offline.
		}
		if(isset($mixed->wseat) && is_array($mixed->wseat)){
			foreach ($mixed->wseat as $value) {
				$this->wseat[]=(string) $value;								//Array of buyer seats allowed to bid on this auction.  Seats are an optional feature of exchange.  For example, [“4”,”34”,”82”,”A45”] indicates that only advertisers using these exchange seats are allowed to bid on the impressions in this auction.
			}
		}
		if(isset($mixed->allimps)){
			$this->allimps=intval($mixed->allimps);							//Flag to indicate whether Exchange can verify that all impressions offered represent all of the impressions available in context (e.g., all impressions available on the web page; all impressions available for a video [pre, mid and postroll spots], etc.) to support road-blocking.  A true value should only be passed if the exchange is aware of all impressions in context for the publisher. “0” means the exchange cannot verify, and “1” means that all impressions represent all impressions available.
		}
		if(isset($mixed->cur) && is_array($mixed->cur)){
			foreach ($mixed->cur as $value) {
				$this->cur[]=(string) $value;								//Array of allowed currencies for bids on this bid request using ISO-4217 alphabetic codes.  If only one currency is used by the exchange, this parameter is not required.
			}
		}
		if(isset($mixed->bcat) && is_array($mixed->bcat)){
			foreach ($mixed->bcat as $value) {
				$this->bcat[]=(string) $value;								//Blocked Advertiser Categories.  Note that there is no existing categorization / taxonomy of advertiser industries. However, as a substitute exchanges may decide to use IAB categories as an approximation (See Table 6.1 Content Categories)
			}
		}
		if(isset($mixed->badv) && is_array($mixed->badv)){
			foreach ($mixed->badv as $value) {
				$this->badv=(string) $value;								//Array of strings of blocked top-level domains of advertisers. For example, {“company1.com”, “company2.com”}.
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 *
 The “imp” object describes the ad position or impression being auctioned.  A single bid request
 can include multiple “imp” objects, a use case for which might be an exchange that supports
 selling all ad positions on a given page as a bundle.  Each “imp” object has a required ID so that
 bids can reference them individually.  An exchange can also conduct private auctions by
 restricting involvement to specific subsets of seats within bidders.
 The Default column dictates how optional parameters should be interpreted if explicit values
 are not provided.
 *
 */
class openRTB_impression {
	/**
	 * @var object
	 */
	var $banner=null;var $video=null;var $ext=null;
	/**
	 * @var mixed
	 */
	var $id='';var $displaymanager;var $displaymanagerver;var $instl=0;var $tagid;var $bidfloor=0;var $bidfloorcur='USD';var $iframebuster=array();

	/**
	 * @param object $mixed
	 */
	function openRTB_impression($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $id;											//A unique identifier for this impression within the context of the bid request (typically, value starts with 1, and increments up to n for n impressions).
		} else return null;
		if(isset($mixed->banner) && is_object($mixed->banner)){
			$this->banner=new openRTB_banner($mixed->banner);				//A reference to a banner object.  Either a banner or video object (or both if the impression could be either) must be included in an impression object.  See Banner Object.
		}
		if(isset($mixed->video) && is_object($mixed->video)){
			/**
			 * @todo: implement video object
			 */
			$this->video=null;												//A reference to a video object.  Either a banner or video object (or both if the impression could be either) must be included in an impression object.  See Video Object.
		}
		if(!isset($mixed->banner) && !isset($mixed->video)) return false;
		if(isset($mixed->displaymanager)){
			$this->displaymanager=(string) $mixed->displaymanager;			//Name of ad mediation partner, SDK technology, or native player responsible for rendering ad (typically video or mobile).  Used by some ad servers to customize ad code by partner.
		}
		if(isset($mixed->displaymanagerver)){
			$this->displaymanagerver=(string) $mixed->displaymanagerver;	//Version of ad mediation partner, SDK technology, or native player responsible for rendering ad (typically video or mobile).  Used by some ad servers to customize ad code by partner
		}
		if(isset($mixed->instl)){
			$this->instl=intval($mixed->instl);								//1 if the ad is interstitial or full screen; else 0 (i.e., no).
		}
		if(isset($mixed->tagid)){
			$this->tagid=(string) $mixed->tagid;							//Identifier for specific ad placement or ad tag that was used to initiate the auction.  This can be useful for debugging of any issues, or for optimization by the buyer.
		}
		if(isset($mixed->bidfloor)){
			$this->bidfloor=floatval($mixed->bidfloor);						//Bid floor for this impression (in CPM of bidfloorcur).
		}
		if(isset($mixed->bidfloorcur)){
			$this->bidfloorcur=(string) $mixed->bidfloorcur;				//If bid floor is specified and multiple currencies supported per bid request, then currency should be specified here using ISO-4217 alphabetic codes. Note, this may be different from bid currency returned by bidder, if this is allowed on an exchange.
		}
		if(isset($mixed->iframebuster) && is_array($mixed->iframebuster)){
			foreach ($mixed->iframebuster as $value){
				$this->iframebuster[]=(string) $value;			//Array of names for supported iframe busters.  Exchange specific.
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The “banner” object must be included directly in the impression object if the impression offered
 for auction is display or rich media, or it may be optionally embedded in the video object to
 describe the companion banners available for the linear or non-linear video ad.  The banner
 object may include a unique identifier; this can be useful if these IDs can be leveraged in the
 VAST response to dictate placement of the companion creatives when multiple companion ad
 opportunities of the same size are available on a page.
 The Default column indicates how optional parameters should be interpreted if explicit values
 are not provided.
 */
class openRTB_banner {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $w;var $h;var $id;var $pos;var $btype=array();var $battr=array();var $mimes=array();var $topframe=0;var $expdir=array();var $api=array();

	/**
	 * @param object $mixed
	 */
	function openRTB_banner($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->w)){
			$this->w=intval($mixed->w);										//Width of the impression in pixels.  Since some ad types are not restricted by size this field is not required, but it’s highly recommended that this information be included when possible.
		}
		if(isset($mixed->h)){
			$this->h=intval($mixed->h);										//Height of the impression in pixels.  Since some ad types are not restricted by size this field is not required, but it’s highly recommended that this information be included when possible.
		}
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//Unique identifier for this banner object. Useful for tracking multiple banner objects (e.g., in companion banner array).  Usually starts with 1, increasing with each object. Combination of impression id banner object should be unique.
		}
		if(isset($mixed->pos)){
			$this->pos=intval($mixed->pos);									//Ad Position. Use Table 6.5
		}
		if(isset($mixed->btype) && is_array($mixed->btype)){
			foreach ($mixed->btype as $value) {
				$this->btype[]=intval($value);								//Blocked creative types.  See Table 6.2 Banner Ad Types.  If blank, assume all types are allowed.
			}
		}
		if(isset($mixed->battr) && is_array($mixed->battr)){
			foreach ($mixed->battr as $value) {
				$this->battr[]=intval($value);								//Blocked creative attributes.  See Table 6.3 Creative Attributes.  If blank assume all types are allowed.
			}
		}
		if(isset($mixed->mimes) && is_array($mixed->mimes)){
			foreach ($mixed->mimes as $value) {
				$this->mimes[]=(string) $value;								//Whitelist of content MIME types supported.  Popular MIME types include, but are not limited to “image/jpg”, “image/gif” and “application/x-shockwave-flash”.
			}
		}
		if(isset($mixed->topframe)){
			$this->topframe=intval($mixed->topframe);						//Specify if the banner is delivered in the top frame or in an iframe.  “0” means it is not in the top frame, and “1” means that it is.
		}
		if(isset($mixed->expdir) && is_array($mixed->expdir)){
			foreach ($mixed->expdir as $value) {
				$this->expdir[]=intval($value);								//Specify properties for an expandable ad.  See Table 6.11 Expandable Direction for possible values.
			}
		}
		if(isset($mixed->api) && is_array($mixed->api)){
			foreach ($mixed->api as $value) {
				$this->api[]=intval($value);								//List of supported API frameworks for this banner. (See Table 6.4 API Frameworks).  If an API is not explicitly listed it is assumed not to be supported.
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 *
 A site object should be included if the ad supported content is part of a website (as opposed to
 an application).  A bid request must not contain both a site object and an app object.
 The site object itself and all of its parameters are optional, so default values are not provided. If
 an optional parameter is not specified, it should be considered unknown.  At a minimum, it’s
 useful to provide a page URL or a site ID, but this is not strictly required.
 *
 */
class openRTB_site {
	/**
	 * @var object
	 */
	var $ext=null;var $publisher=null;var $content=null;
	/**
	 * @var mixed
	 */
	var $id;var $name;var $domain;var $cat=array();var $sectioncat=array();var $pagecat=array();var $page;var $privacypolicy;var $ref;var $search;var $keywords;

	/**
	 * @param object $mixed
	 */
	function openRTB_site($mixed) {
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){												//Site ID on the exchange.
			$this->id=(string) $mixed->id;
		}
		if(isset($mixed->name)){
			$this->name=(string) $mixed->name;								//Site name (may be masked at publisher’s request).
		}
		if(isset($mixed->domain)){
			$this->domain=(string) $mixed->domain; 							//Domain of the site, used for advertiser side blocking.  For example, “foo.com”.
		}
		if(isset($mixed->cat) && is_array($mixed->cat)){
			foreach ($mixed->cat as $value) {
				$this->cat[]=(string) $value;								//Array of IAB content categories for the overall site.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->sectioncat) && is_array($mixed->sectioncat)){
			foreach ($mixed->sectioncat as $value) {
				$this->sectioncat[]=(string) $value;						//Array of IAB content categories for the current subsection of the site.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->pagecat) && is_array($mixed->pagecat)){
			foreach ($mixed->pagecat as $value) {
				$this->pagecat[]=(string) $value;							//Array of IAB content categories for the current page.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->page)){
			$this->page=(string)$mixed->page;								//URL of the page where the impression will be shown
		}
		if(isset($mixed->privacypolicy)){
			$this->privacypolicy=intval($mixed->privacypolicy);				//Specifies whether the site has a privacy policy. “1” means there is a policy. “0” means there is not.
		}
		if(isset($mixed->ref)){
			$this->ref=(string) $mixed->ref;								//Referrer URL that caused navigation to the current page.
		}
		if(isset($mixed->search)){
			$this->search=(string) $mixed->search;							//Search string that caused navigation to the current page.
		}
		if(isset($mixed->publisher) && is_object($mixed->publisher)){
			$this->publisher=new openRTB_publisher($mixed->publisher);		//See Publisher Object
		}
		if(isset($mixed->content) && is_object($mixed->content)){
			$this->content=new openRTB_content($mixed->content);			//See Content Object
		}
		if(isset($mixed->keywords)){
			$this->keywords=(string) $mixed->keywords;						//List of keywords describing this site in a comma separated string
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The content object itself and all of its parameters are optional, so default values are not
 provided.  If an optional parameter is not specified, it should be considered unknown.  This
 object describes the content in which the impression will appear (may be syndicated or non-
 syndicated content).
 This object may be useful in the situation where syndicated content contains impressions and
 does not necessarily match the publisher’s general content.  The exchange might or might not
 have knowledge of the page where the content is running, as a result of the syndication
 method.  (For example, video impressions embedded in an iframe on an unknown web property
 or device.)
 */
class openRTB_content {
	/**
	 * @var object
	 */
	var $ext=null;var $producer=null;
	/**
	 * @var mixed
	 */
	var $id;var $episode;var $title;var $series;var $season;var $url;var $cat=array();var $videoquality;var $keywords;var $contentrating;var $userrating;var $context;var $livestream;var $sourcerelationship;var $len;var $qagmediarating; var $embeddable;var $language;

	/**
	 * @param object $mixed
	 */
	function openRTB_content($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//ID uniquely identifying the content
		}
		if(isset($mixed->episode)){
			$this->episode=intval($mixed->episode);							//Content episode number (typically applies to video content).
		}
		if(isset($mixed->title)){
			$this->title=(string) $mixed->title;							//Content title; Video examples: “Search Committee” (television) or “A New Hope” (movie) or “Endgame” (made for web) Non-video example:  “Why an Antarctic Glacier Is Melting So Quickly” (Time magazine article)
		}
		if(isset($mixed->series)){
			$this->series=(string) $mixed->series;							//Content series.  Video examples: “The Office” (television) or “Star Wars” (movie) or “Arby ‘N’ The Chief” (made for web) Non-video example:  “Ecocentric”  (Time magazine blog)
		}
		if(isset($mixed->season)){
			$this->season=(string) $mixed->season;							//Content season.  E.g., “Season 3” (typically applies to video content).
		}
		if(isset($mixed->url)){
			$this->url=(string) $mixed->url;								// Original URL of the content, for buy-side contextualization or review
		}
		if(isset($mixed->cat) && is_array($mixed->cat)){
			foreach ($mixed->cat as $value) {
				$this->cat[]=(string) $value;								//Array of IAB content categories for the content.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->videoquality)){
			$this->videoquality=intval($mixed->videoquality);		  		//Video quality per the IAB’s classification.  See Table 6.14 Video Quality.
		}
		if(isset($mixed->keywords)){
			$this->keywords=(string) $mixed->keywords;						//Comma separated list of keywords describing the content
		}
		if(isset($mixed->userrating)){
			$this->userrating=(string) $mixed->userrating;					//User rating of the content (e.g., number of stars, likes, etc.).
		}
		if(isset($mixed->context)){
			$this->context=(string) $mixed->context;						//Specifies the type of content (game, video, text, etc.).  See Table 6.13 Content Context.
		}
		if(isset($mixed->livestream)){
			$this->livestream=intval($mixed->livestream);					//Is content live?  E.g., live video stream, live blog.  “1” means content is live.  “0” means it is not live.
		}
		if(isset($mixed->sourcerelationship)){
			$this->sourcerelationship=intval($mixed->sourcerelationship);	//1 for “direct”; 0 for “indirect”
		}
		if(isset($mixed->producer) && is_object($mixed->producer)){
			$this->producer=new openRTB_producer($mixed->producer);			//See Producer Object
		}
		if(isset($mixed->len)){
			$this->len=intval($mixed->len);									//Length of content (appropriate for video or audio) in seconds.
		}
		if(isset($mixed->qagmediarating)){
			$this->qagmediarating=intval($mixed->qagmediarating);			//Media rating of the content, per QAG guidelines. See Table 0 QAG Media Ratings for list of possible values
		}
		if(isset($mixed->embeddable)){
			$this->embeddable=intval($mixed->embeddable);					//From QAG Video Addendum.  If content can be embedded (such as an embeddable video player) this value should be set to “1”.  If content cannot be embedded, then this should be set to “0”.
		}
		if(isset($mixed->language)){
			$this->language=(string) $mixed->language;						//Language of the content.  Use alpha-2/ISO 639-1 codes.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The publisher object itself and all of its parameters are optional, so default values are not
 provided.  If an optional parameter is not specified, it should be considered unknown.
 */
class openRTB_publisher {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $id;var $name;var $cat=array();var $domain;

	/**
	 * @param object $mixed
	 */
	function openRTB_publisher($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//Publisher ID on the exchange.
		}
		if(isset($mixed->name)){
			$this->name=(string) $mixed->name;								//Publisher name (may be masked at publisher’s request).
		}
		if(isset($mixed->cat) && is_array($mixed->cat)){
			foreach ($mixed->cat as $value) {
				$this->cat[]=(string) $value;									//Array of IAB content categories for the publisher.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->domain)){
			$this->domain=(string) $mixed->domain;							//Publisher’s highest level domain name, for example “foopub.com”.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The producer is useful when content where the ad is shown is syndicated, and may appear on a
 completely different publisher.  The producer object itself and all of its parameters are optional,
 so default values are not provided.  If an optional parameter is not specified, it should be
 considered unknown.   This object is optional, but useful if the content producer is different
 from the site publisher.
 */
class openRTB_producer {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $id;var $name;var $cat=array();var $domain;

	/**
	 * @param object $mixed
	 */
	function openRTB_producer($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//Content producer or originator ID.  Useful if content is syndicated, and may be posted on a site using embed tags.
		}
		if(isset($mixed->name)){
			$this->name=(string) $mixed->name;								//Content producer or originator name (e.g., “Warner Bros”).
		}
		if(isset($mixed->cat) && is_array($mixed->cat)){
			foreach ($mixed->cat as $value) {
				$this->cat[]=(string) $value;								//Array of IAB content categories for the content producer.  See Table 6.1 Content Categories.
			}
		}
		if(isset($mixed->domain)){
			$this->domain=(string) $mixed->domain;							//URL of the content producer.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 *
 * The “device” object provides information pertaining to the device including its hardware,
 platform, location, and carrier.  This device can refer to a mobile handset, a desktop computer,
 set top box or other digital device.
 The device object itself and all of its parameters are optional, so default values are not provided.
 If an optional parameter is not specified, it should be considered unknown.
 In general, the most essential fields are either the IP address (to enable geo-lookup for the
 bidder), or providing geo information directly in the geo object.
 *
 */
class openRTB_device {
	/**
	 * @var object
	 */
	var $ext=null;var $geo=null;
	/**
	 * @var mixed
	 */
	var $dnt;var $ua;var $ip;var $didsha1;var $didmd5;var $dpidsha1;var $dpidmd5;var $ipv6;var $carrier;var $language;var $make;var $model;var $os;var $Osv;var $Js;var $connectiontype;var $devicetype;var $flashver;

	/**
	 * @param object $mixed
	 */
	function openRTB_device($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->dnt)){
			$this->dnt=intval($mixed->dnt);									//If “0”, then do not track Is set to false, if “1”, then do no track is set to true in browser.
		}
		if(isset($mixed->ua)){
			$this->ua=(string) $mixed->ua;									// Browser user agent string.
		}
		if(isset($mixed->ip)){
			$this->ip=(string) $mixed->ip;									//IPv4 address closest to device.
		}
		if(isset($mixed->geo) && is_object($mixed->geo)){
			$this->geo=new openRTB_geo($mixed->geo);						//Geography as derived from the device’s location services (e.g., cell tower triangulation, GPS) or IP address.  See Error! Reference source ot found..
		}
		if(isset($mixed->didsha1)){
			$this->didsha1=(string) $mixed->didsha1;						//SHA1 hashed device ID; IMEI when available, else MEID or ESN.  OpenRTB’s preferred method for device ID hashing is SHA1.
		}
		if(isset($mixed->didmd5)){
			$this->didmd5=(string) $mixed->didmd5;							//MD5 hashed device ID; IMEI when available, else MEID or ESN.  Should be interpreted as case insensitive.
		}
		if(isset($mixed->dpidsha1)){
			$this->dpidsha1=(string) $mixed->dpidsha1;						//SHA1 hashed platform-specific ID (e.g., Android ID or UDID for iOS).  OpenRTB’s preferred method for device ID hash is SHA1.
		}
		if(isset($mixed->dpidmd5)){
			$this->dpidmd5=(string) $mixed->dpidmd5;						//MD5 hashed platform-specific ID (e.g., Android ID or UDID for iOS).  Should be interpreted as case insensitive.
		}
		if(isset($mixed->ipv6)){
			$this->ipv6=(string) $mixed->ipv6;								//IP address in IPv6.
		}
		if(isset($mixed->carrier)){
			$this->carrier=(string) $mixed->carrier;						//Carrier or ISP derived from the IP address.  Should be specified using Mobile Network Code (MNC) http://en.wikipedia.org/wiki/Mobile_Network_Code
		}
		if(isset($mixed->language)){
			$this->language=(string) $mixed->language;						//Browser language; use alpha-2/ISO 639-1 codes.
		}
		if(isset($mixed->make)){
			$this->make=(string) $mixed->make;								//Device make (e.g., “Apple”).
		}
		if(isset($mixed->model)){
			$this->model=(string) $mixed->model;							//Device model (e.g., “iPhone”)
		}
		if(isset($mixed->os)){
			$this->os=(string) $mixed->os;									//Device operating system (e.g., “iOS”).
		}
		if(isset($mixed->Osv)){
			$this->Osv=(string) $mixed->Osv;								//Device operating system version (e.g., “3.1.2”).
		}
		if(isset($mixed->Js)){
			$this->Js=intval($mixed->Js);									//“1” if the device supports JavaScript; else “0”.
		}
		if(isset($mixed->connectiontype)){
			$this->connectiontype=intval($mixed->connectiontype);			//Return the detected data connection type for the device.  See Table 6.10 Connection Type.
		}
		if(isset($mixed->devicetype)){
			$this->devicetype=intval($mixed->devicetype);					//Return the device type being used.  See Table 6.16 Device Type.
		}
		if(isset($mixed->flashver)){
			$this->flashver=(string) $mixed->flashver;						//Return the Flash version detected.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 *
 The geo object itself and all of its parameters are optional, so default values are not provided. If
 an optional parameter is not specified, it should be considered unknown.
 Note that the Geo Object may appear in one or both the Device Object and the User Object.
 This is intentional, since the information may be derived from either a device-oriented source
 (such as IP geo lookup), or by user registration information (for example provided to a publisher
 through a user registration).   If the information is in conflict, it’s up to the bidder to determine
 which information to use.
 *
 */
class openRTB_geo {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $lat;var $lon;var $country;var $region;var $regionfips104;var $metro;var $city;var $zip;var $type;

	/**
	 * @param object $mixed
	 */
	function openRTB_geo($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->lat)){
			$this->lat=floatval($mixed->lat);								//Latitude from -90 to 90. South is negative.   This should only be passed if known to be accurate (For example, not the centroid of a postal code).
		}
		if(isset($mixed->lon)){
			$this->lon=floatval($mixed->lon);								//Longitude from -180 to 180. West is negative.  This should only be passed if known to be accurate.
		}
		if(isset($mixed->country)){
			$this->country=(string) $mixed->country;						//Country using ISO-3166-1 Alpha-3.
		}
		if(isset($mixed->region)){
			$this->region=(string) $mixed->region;							//Region using ISO 3166-2
		}
		if(isset($mixed->regionfips104)){
			$this->regionfips104=(string) $mixed->regionfips104;			//Region of a country using fips 10-4 notation (alternative to ISO 3166-2)
		}
		if(isset($mixed->metro)){
			$this->metro=(string) $mixed->metro;							//Pass the metro code (see http://code.google.com/apis/adwords/docs/appendix/metrocodes.html).  Metro codes are similar to but not exactly the same as Nielsen DMAs.
		}
		if(isset($mixed->city)){
			$this->city=(string) $mixed->city;								//City using United Nations Code for Trade and Transport Locations (http://www.unece.org/cefact/locode/service/location.htm)
		}
		if(isset($mixed->zip)){
			$this->zip=(string) $mixed->zip;								//Zip/postal code
		}
		if(isset($mixed->type)){
			$this->type=intval($mixed->type);								//ndicate the source of the geo data (GPS, IP address, user provided).  See Table 6.15 Location Type for a list of potential values.   Type should be provided when lat/lon is provided.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 *
 The “user” object contains information known or derived about the human user of the device.
 Note that the user ID is an exchange artifact (refer to the “device” object for hardware or
 platform derived IDs) and may be subject to rotation policies.   However, this user ID must be
 stable long enough to serve reasonably as the basis for frequency capping.
 The user object itself and all of its parameters are optional, so default values are not provided.
 If an optional parameter is not specified, it should be considered unknown.
 If device ID is used as a proxy for unique user ID, use the device object.
 *
 */
class openRTB_user {
	/**
	 * @var object
	 */
	var $ext=null;var $geo=null;var $data=array();
	/**
	 * @var mixed
	 */
	var $id;var $buyerid;var $yob;var $gender;var $keywords;var $customdata;

	/**
	 * @param object $mixed
	 */
	function openRTB_user($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//Unique consumer ID of this user on the exchange
		}
		if(isset($mixed->buyeruid)){
			$this->buyeruid=(string) $mixed->buyeruid;						//Buyer’s user ID for this user as mapped by exchange for the buyer.
		}
		if(isset($mixed->yob)){
			$this->yob=intval($mixed->yob);									//Year of birth as a 4-digit integer.
		}
		if(isset($mixed->gender)){
			$this->gender=(string) $mixed->gender;							//Gender as “M” male, “F” female, “O” Other.   (Null indicates unknown)
		}
		if(isset($mixed->keywords)){
			$this->keywords=(string) $mixed->keywords;						//Comma separated list of keywords of consumer interests or intent.
		}
		if(isset($mixed->customdata)){
			$this->customdata=(string) $mixed->customdata;					//If supported by the exchange, this is custom data that the bidder had stored in the exchange’s cookie.   The string may be in base85 cookie safe characters, and be in any format.  This may useful for storing user features. Note: Proper JSON encoding must be used to include “escaped” quotation marks.
		}
		if(isset($mixed->geo) && is_object($mixed->geo)){
			$this->geo=new openRTB_geo($mixed->geo);						//Home geo for the user (e.g., based off of registration data); this is different from the current location of the access device (that is defined by the geo object embedded in the Device Object);  see Error! Reference source ot found.
		}
		if(isset($mixed->data) && is_array($mixed->data)){
			foreach ($mixed->data as $value) {
				if (is_object($value))
				$this->data[]=new openRTB_data($mixed->data);				//See Data Object
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The data and segment objects together allow data about the user to be passed to bidders in the
 bid request.  This data may be from multiple sources (e.g., the exchange itself, third party
 providers) as specified by the data object ID field.  A bid request can mix data objects from
 multiple providers.
 The data object itself and all of its parameters are optional, so default values are not provided.
 If an optional parameter is not specified, it should be considered unknown.
 */
class openRTB_data {
	/**
	 * @var object
	 */
	var $ext=null;var $segment=array();
	/**
	 * @var mixed
	 */
	var $id;var$name;

	/**
	 * @param object $mixed
	 */
	function openRTB_data($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $id;											//Exchange specific ID for the data provider
		}
		if(isset($mixed->name)){
			$this->name=(string) $mixed->name;								//Data provider name
		}
		if(isset($mixed->segment) && is_array($mixed->segment)){
			foreach ($mixed->segment as $value) {
				if (is_object($value))
				$this->segment[]=new openRTB_segment($value);				//Array of segment objects
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * The data and segment objects together allow data about the user to be passed to bidders in the
 bid request.  Segment objects convey specific units of information from the provider identified
 in the parent data object.
 The segment object itself and all of its parameters are optional, so default values are not
 provided; if an optional parameter is not specified, it should be considered unknown.
 */
class openRTB_segment {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $id;var $name;var $value;

	/**
	 * @param object $mixed
	 */
	function openRTB_segment($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//ID of a data provider’s segment applicable to the user
		}
		if(isset($mixed->name)){
			$this->name=(string) $mixed->name;								//Name of a data provider’s segment applicable to the user
		}
		if(isset($mixed->value)){
			$this->value=(string) $mixed->value;							//String representing the value of the segment.  The method for transmitting this data should be negotiated offline with the data provider.  For example for gender, “male”, or “female”, for age, “30-40”)
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**#@-*/
/**#@+
 * bid response objects
 */
/**
 * The top-level bid response object is defined below.  The “id” attribute is a reflection of the bid
 request ID for logging purposes.  Similarly, “bidid” is an optional response tracking ID for
 bidders.  If specified, it can be included in the subsequent win notice call if the bidder wins.  At
 least one “seatbid” object is required, which contains a bid on at least one impression.  Other
 attributes are optional since an exchange may establish default values.
 No-Bids on all impressions should be indicated as a HTTP 204 response.   For no-bids on specific
 impressions, the bidder should omit these from the bid response.
 */
class openRTBresponse {
	/**
	 * @var object
	 */
	var $ext=null;var $seatbid=array();
	/**
	 * @var mixed
	 */
	var $id;var $bidid;var $cur='USD';var $customerdata;

	/**
	 * @param object $mixed
	 */
	function openRTBresponse($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//ID of the bid request.
		} else return null;
		if(isset($mixed->seatbid) && is_array($mixed->seatbid)){
			foreach ($mixed->seatbid as $value) {
				if(is_object($value))
				$this->seatbid[]=new openRTB_seatbid($value);				//Array of seatbid objects.
			}
		} else return null;
		if(isset($mixed->bidid)){
			$this->bidid=(string) $mixed->bidid;							//Bid response ID to assist tracking for bidders.  This value is chosen by the bidder for cross-reference.
		}
		if(isset($mixed->cur)){
			$this->cur=(string) $mixed->cur;								//Bid currency using ISO-4217 alphabetic codes; default is “USD”.
		}
		if(isset($mixed->customdata)){
			$this->customdata=(string) $mixed->customdata;					//This is an optional feature, which allows a bidder to set data in the exchange’s cookie.  The string may be in base85 cookie safe characters, and be in any format.  This may be useful for storing user features. Note: Proper JSON encoding must be used to include “escaped” quotation marks.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * A bid response can contain multiple “seatbid” objects, each on behalf of a different bidder seat.
 Since a bid request can include multiple impressions, each “seatbid” object can contain multiple
 bids each pertaining to a different impression on behalf of a seat.  Thus, each “bid” object must
 include the impression ID to which it pertains as well as the bid price.  The “group” attribute can
 be used to specify if a seat is willing to accept any impressions that it can win (default) or if it is
 only interested in winning any if it can win them all (i.e., all or nothing).
 */
class openRTB_seatbid {
	/**
	 * @var object
	 */
	var $ext=null;var $bid=array();
	/**
	 * @var mixed
	 */
	var $seat;var $group=0;

	/**
	 * @param object $mixed
	 */
	function openRTB_seatbid($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->bid) && is_array($mixed->bid)){
			foreach ($mixed->bid as $value) {
				if(is_object($value))
				$this->bid[]=new openRTB_bid($value);						//Array of bid objects; each bid object relates to an imp object in the bid request.   Note that, if supported by an exchange, one imp object can have many bid objects.
			}
		} else return null;
		if(isset($mixed->seat)){
			$this->seat=(string) $mixed->seat;								//ID of the bidder seat on whose behalf this bid is made.
		}
		if(isset($mixed->group)){
			$this->group=intval($mixed->group);								//“1” means impressions must be won-lost as a group; default is “0”.
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**
 * For each bid, the “nurl” attribute contains the win notice URL.  If the bidder wins the impression,
 the exchange calls this notice URL a) to inform the bidder of the win and b) to convey certain
 information using substitution macros (see Section 4.6 Substitution Macros).
 The “adomain” attribute can be used to check advertiser block list compliance.  The “iurl”
 attribute can provide a link to an image that is representative of the campaign’s content
 (irrespective of whether the campaign may have multiple creatives).  This enables human review
 for spotting inappropriate content.  The “cid” attribute can be used to block ads that were
 previously identified as inappropriate; essentially a safety net beyond the block lists.  The “crid”
 attribute can be helpful in reporting creative issues back to bidders.  Finally, the “attr” array
 indicates the creative attributes that describe the ad to be served.
 */
class openRTB_bid {
	/**
	 * @var object
	 */
	var $ext=null;
	/**
	 * @var mixed
	 */
	var $id;var $impid;var $price;var $adid;var $nurl;var $adm;var $adomain=array();var $iurl;var $cid;var $crid;var $attr=array();

	/**
	 * @param object $mixed
	 */
	function openRTB_bid($mixed){
		if(!is_object($mixed)) return null;
		if(isset($mixed->id)){
			$this->id=(string) $mixed->id;									//ID for the bid object chosen by the bidder for tracking and debugging purposes.  Useful when multiple bids are submitted for a single impression for a given seat
		} else return null;
		if(isset($mixed->impid)){
			$this->impid=(string) $mixed->impid;							//ID of the impression object to which this bid applies.
		} else return null;
		if(isset($mixed->price)){
			$this->price=floatval($mixed->price);							//Bid price in CPM.  WARNING/Best Practice Note: Although this value is a float, OpenRTB strongly suggests using integer math for accounting to avoid rounding errors.
		} else return null;
		if(isset($mixed->adid)){
			$this->adid=(string) $mixed->adid;								//ID that references the ad to be served if the bid wins.
		}
		if(isset($mixed->nurl)){
			$this->nurl=(string) $mixed->nurl;								//Win notice URL.  Note that ad markup is also typically, but not necessarily, returned via this URL.
		}
		if(isset($mixed->adm)){
			$this->adm=(string) $mixed->adm;								//Actual ad markup.  XHTML if a response to a banner object, or VAST XML if a response to a video object.
		}
		if(isset($mixed->adomain) && is_array($mixed->adomain)){
			foreach ($mixed->adomain as $value) {
				$this->adomain[]=(string) $value;							//Advertiser’s primary or top-level domain for advertiser checking.  This can be a list of domains if there is a rotating creative.  However, exchanges may mandate that only one landing domain is allowed.
			}
		}
		if(isset($mixed->iurl)){
			$this->iurl=(string) $mixed->iurl;								//Sample image URL (without cache busting) for content checking
		}
		if(isset($mixed->cid)){
			$this->cid=(string) $mixed->cid;								//Campaign ID or similar that appears within the ad markup
		}
		if(isset($mixed->crid)){
			$this->crid=(string) $mixed->crid;								//Creative ID for reporting content issues or defects.  This could also be used as a reference to a creative ID that is posted with an exchange.
		}
		if(isset($mixed->attr) && is_array($mixed->attr)){
			foreach ($mixed->attr as $value) {
				$this->attr[]=intval($value);								//Array of creative attributes.  See Table 6.3 Creative Attributes.
			}
		}
		if(isset($mixed->ext) && is_object($mixed->ext)){
			$this->ext=openRTB_Staticfunctions::getExtClass($mixed->ext);	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
		}
	}
}
/**#@-*/
?>