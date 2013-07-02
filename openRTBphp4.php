<?php
/**
 * openRTB v2.1 implementation for php4
 * 
 * @method JSON
 * @uses json.php http://pear.php.net/pepr/pepr-proposal-show.php?id=198
 * @todo implement banner, video, app, content, publisher, producer objects.
 * @author ulkas
 * @version 0.1
 **/

/**
 * @see http://pear.php.net/pepr/pepr-proposal-show.php?id=198
 */
include 'json.php';

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
		if (isset($_GET['debug']))
		{
			return $_GET['debug'];
		}

		if (isset($_POST['debug']))
		{
			return $_POST['debug'];
		}
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
	function openRTBrequest($id){
		$this->id=$id;
		//@TODO: idecka pre impresie odniekial zobrat
		$this->imp=array(new openRTB_impression());		//Array of impression objects. Multiple impression auctions may be specified in a single bid request.  At least one impression is required for a valid bid request.
		$this->site=new openRTB_site();					//See Site Object
		$this->device=new openRTB_device();				//See Device Object
		$this->user=new openRTB_user();					//See User Object
		$this->at=2;									//Auction Type. If “1”, then first price auction.  If “2”, then second price auction.  Additional auction types can be defined as per the exchange’s business rules.  Exchange specific rules should be numbered over 500.
		$this->tmax=0;									//Maximum amount of time in milliseconds to submit a bid (e.g., 120 means the bidder has 120ms to submit a bid before the auction is complete).  If this value never changes across an exchange, then the exchange can supply this information offline.
		$this->wseat=array();							//Array of buyer seats allowed to bid on this auction.  Seats are an optional feature of exchange.  For example, [“4”,”34”,”82”,”A45”] indicates that only advertisers using these exchange seats are allowed to bid on the impressions in this auction.
		$this->allimps=0;								//Flag to indicate whether Exchange can verify that all impressions offered represent all of the impressions available in context (e.g., all impressions available on the web page; all impressions available for a video [pre, mid and postroll spots], etc.) to support road-blocking.  A true value should only be passed if the exchange is aware of all impressions in context for the publisher. “0” means the exchange cannot verify, and “1” means that all impressions represent all impressions available.
		$this->cur=array();								//Array of allowed currencies for bids on this bid request using ISO-4217 alphabetic codes.  If only one currency is used by the exchange, this parameter is not required.
		$this->bcat=array();							//Blocked Advertiser Categories.  Note that there is no existing categorization / taxonomy of advertiser industries. However, as a substitute exchanges may decide to use IAB categories as an approximation (See Table 6.1 Content Categories)
		$this->badv=array();							//Array of strings of blocked top-level domains of advertisers. For example, {“company1.com”, “company2.com”}.
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.

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
	function openRTB_impression($id){
		$this->id=$id;					//A unique identifier for this impression within the context of the bid request (typically, value starts with 1, and increments up to n for n impressions).
		/**#@+
		 * @todo banner, video, displaymanager, displaymanagerver objects not implemented, we do not use them yet.
		 */
		$this->banner=null;				//A reference to a banner object.  Either a banner or video object (or both if the impression could be either) must be included in an impression object.  See Banner Object.
		$this->video=null;				//A reference to a video object.  Either a banner or video object (or both if the impression could be either) must be included in an impression object.  See Video Object.
		$this->displaymanager=null;		//Name of ad mediation partner, SDK technology, or native player responsible for rendering ad (typically video or mobile).  Used by some ad servers to customize ad code by partner.
		$this->displaymanagerver=null;	//Version of ad mediation partner, SDK technology, or native player responsible for rendering ad (typically video or mobile).  Used by some ad servers to customize ad code by partner
		/**#@-*/
		$this->instl=0;					//1 if the ad is interstitial or full screen; else 0 (i.e., no).
		if(openRTB_Staticfunctions::isDebug()){
			$this->tagid=openRTB_Staticfunctions::isDebug();//Identifier for specific ad placement or ad tag that was used to initiate the auction.  This can be useful for debugging of any issues, or for optimization by the buyer.
		}
		$this->bidfloor=0.0;			//Bid floor for this impression (in CPM of bidfloorcur).
		$this->bidfloorcur='USD';		//If bid floor is specified and multiple currencies supported per bid request, then currency should be specified here using ISO-4217 alphabetic codes. Note, this may be different from bid currency returned by bidder, if this is allowed on an exchange.
		$this->iframebuster=array();	//Array of names for supported iframe busters.  Exchange specific.
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_site($id) {
		$this->id=$id;
		$this->name='';					//Site name (may be masked at publisher’s request).
		$this->domain=''; 				//Domain of the site, used for advertiser side blocking.  For example, “foo.com”.
		$this->cat=array();				//Array of IAB content categories for the overall site.  See Table 6.1 Content Categories.
		$this->sectioncat=array();		//Array of IAB content categories for the current subsection of the site.  See Table 6.1 Content Categories.
		$this->pagecat=array();			//Array of IAB content categories for the current page.  See Table 6.1 Content Categories.
		$this->page='';					//URL of the page where the impression will be shown
		$this->privacypolicy=0;			//Specifies whether the site has a privacy policy. “1” means there is a policy. “0” means there is not.
		$this->ref='';					//Referrer URL that caused navigation to the current page.
		$this->search='';				//Search string that caused navigation to the current page.
		$this->publisher=new openRTB_publisher();			//See Publisher Object
		$this->content=new openRTB_content();				//See Content Object
		$this->keywords='';				//List of keywords describing this site in a comma separated string
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_device(){
		$this->dnt=0;					//If “0”, then do not track Is set to false, if “1”, then do no track is set to true in browser.
		$this->ua='';					// Browser user agent string.
		$this->ip='';					//IPv4 address closest to device.
		$this->geo=new stdClass();		//Geography as derived from the device’s location services (e.g., cell tower triangulation, GPS) or IP address.  See Error! Reference source ot found..
		$this->didsha1='';				//SHA1 hashed device ID; IMEI when available, else MEID or ESN.  OpenRTB’s preferred method for device ID hashing is SHA1.
		$this->didmd5='';				//MD5 hashed device ID; IMEI when available, else MEID or ESN.  Should be interpreted as case insensitive.
		$this->dpidsha1='';				//SHA1 hashed platform-specific ID (e.g., Android ID or UDID for iOS).  OpenRTB’s preferred method for device ID hash is SHA1.
		$this->dpidmd5='';				//MD5 hashed platform-specific ID (e.g., Android ID or UDID for iOS).  Should be interpreted as case insensitive.
		$this->ipv6='';					//IP address in IPv6.
		$this->carrier='';				//Carrier or ISP derived from the IP address.  Should be specified using Mobile Network Code (MNC) http://en.wikipedia.org/wiki/Mobile_Network_Code
		$this->language='';				//Browser language; use alpha-2/ISO 639-1 codes.
		$this->make='';					//Device make (e.g., “Apple”).
		$this->model='';				//Device model (e.g., “iPhone”)
		$this->os='';					//Device operating system (e.g., “iOS”).
		$this->osv='';					//Device operating system version (e.g., “3.1.2”).
		$this->Js=1;					//“1” if the device supports JavaScript; else “0”.
		$this->connectiontype=0;		//Return the detected data connection type for the device.  See Table 6.10 Connection Type.
		$this->devicetype=0;			//Return the device type being used.  See Table 6.16 Device Type.
		$this->flashver='';				//Return the Flash version detected.
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_geo(){
		$this->lat=0.0;					//Latitude from -90 to 90. South is negative.   This should only be passed if known to be accurate (For example, not the centroid of a postal code).
		$this->lon=0.0;					//Longitude from -180 to 180. West is negative.  This should only be passed if known to be accurate.
		$this->country='';				//Country using ISO-3166-1 Alpha-3.
		$this->region='';				//Region using ISO 3166-2
		$this->regionfips104='';		//Region of a country using fips 10-4 notation (alternative to ISO 3166-2)
		$this->metro='';				//Pass the metro code (see http://code.google.com/apis/adwords/docs/appendix/metrocodes.html).  Metro codes are similar to but not exactly the same as Nielsen DMAs.
		$this->city='';					//City using United Nations Code for Trade and Transport Locations (http://www.unece.org/cefact/locode/service/location.htm)
		$this->zip='';					//Zip/postal code
		$this->type=0;					//ndicate the source of the geo data (GPS, IP address, user provided).  See Table 6.15 Location Type for a list of potential values.   Type should be provided when lat/lon is provided.
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_user($id){
		$this->id=$id;					//Unique consumer ID of this user on the exchange
		$this->buyeruid='';				//Buyer’s user ID for this user as mapped by exchange for the buyer.
		$this->yob=0;					//Year of birth as a 4-digit integer.
		$this->gender=null;				//Gender as “M” male, “F” female, “O” Other.   (Null indicates unknown)
		$this->keywords='';				//Comma separated list of keywords of consumer interests or intent.
		$this->customdata='';			//If supported by the exchange, this is custom data that the bidder had stored in the exchange’s cookie.   The string may be in base85 cookie safe characters, and be in any format.  This may useful for storing user features. Note: Proper JSON encoding must be used to include “escaped” quotation marks.
		$this->geo=new stdClass();		//Home geo for the user (e.g., based off of registration data); this is different from the current location of the access device (that is defined by the geo object embedded in the Device Object);  see Error! Reference source ot found.
		$this->data=array(new openRTB_data());				//See Data Object
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_data($id){
		$this->id=$id;					//Exchange specific ID for the data provider
		$this->name='';					//Data provider name
		$this->segment=array(new openRTB_segment());		//Array of segment objects
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
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
	function openRTB_segment($id){
		$this->id=$id;					//ID of a data provider’s segment applicable to the user
		$this->name='';					//Name of a data provider’s segment applicable to the user
		$this->value='';				//String representing the value of the segment.  The method for transmitting this data should be negotiated offline with the data provider.  For example for gender, “male”, or “female”, for age, “30-40”)
		$this->ext=openRTB_Staticfunctions::getExtClass();	//This object is a placeholder that may contain custom JSON agreed to by the parties in an OpenRTB transaction to support flexibility beyond the standard defined in this specification.
	}
}
?>