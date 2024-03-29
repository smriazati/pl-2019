<?php /**/ ?><?php

class Ghetto_XML_Object {
	function Ghetto_XML_Object( $args = null, $attributes = null ) {
		if ( get_object_vars( $this ) )
			$this->___restrict = true;
		else
			$this->___restrict = false;

		if ( !is_null( $args ) )
			$this->set_args( $args );
		if ( !is_array( $attributes ) )
			return false;

		$atts = array();
		foreach ( $attributes as $key => $value )
			$atts["_$key"] = $value;

		$this->set_args( $atts );
	}

	function xml( $prepend_ns = true, $pad = 0 ) {
		$x = '';
		$atts = get_object_vars( $this );

		$ns = $atts['___ns'];
		if ( $prepend_ns )
			$name = "$ns:{$atts['___name']}";
		else
			$name = $atts['___name'];

		$_prepend_ns = $prepend_ns;

		$prepend_ns = 'all' === $prepend_ns;

		// added this to remove the Warning ( PHP Notice:  Undefined index ) in following condition
		if ( !isset( $atts['___cdata'] ) )
			$atts['___cdata'] = '';
		
		if ( !$cdata = $atts['___cdata'] )
			$cdata = array();

		$x = "<$name";
		
		if ( isset( $atts['___content'] ) ) {
			$inner = in_array( '___content', $cdata ) ? '<![CDATA[' . $atts['___content'] . ']]>' : $atts['___content'];
			$empty = false;
		} else {
			$inner = "\n";
			$empty = true;
		}

		unset($atts['___ns'], $atts['___name'], $atts['___content'], $atts['___ns_full'], $atts['___restrict'], $atts['___cdata']);

		$_pad = str_repeat( "\t", $pad + 1 );

		foreach ( $atts as $key => $value ) {			
			if ( is_null( $value ) )
				continue;
			if ( '_' == $key[0] ) {
				$key = substr( $key, 1 );
				$x .= " $key='$value'";
				continue;
			}

			$_key = $key;
			if ( $prepend_ns )
				$key = "$ns:$key";

			$empty = false;
			if ( false === $value ) {
				$inner .= "$_pad<$key />\n";
			}  
			elseif ( is_array( $value ) ) {
				foreach ( $value as $array_value ) {
					if ( is_a( $array_value, 'Ghetto_XML_Object' ) )
						$inner .= $_pad . $array_value->xml( $_prepend_ns, $pad + 1 ) . "\n";
					else
						$inner .= in_array( $_key, $cdata ) ? "$_pad<$key>" . '<![CDATA[' . $array_value . ']]>' . "</$key>\n" : "$_pad<$key>$array_value</$key>\n";
				}
			} 
			else {
				if ( is_a( $value, 'Ghetto_XML_Object' ) )
					$inner .= $_pad . $value->xml( $_prepend_ns, $pad + 1 ) . "\n";
				else{
					$inner .= in_array( $_key, $cdata ) ? "$_pad<$key>" . '<![CDATA[' . $value . ']]>' . "</$key>\n" : "$_pad<$key>$value</$key>\n";
				}
			}
		}
		if ( $empty )
			return $x . ' />';
		if ( "\n" == substr( $inner, -1 ) )
			$inner .= str_repeat( "\t", $pad  );

		return $x . ">$inner</$name>";
	}

	function set_args( $array ) {
		if ( is_scalar( $array ) ) {
			$this->___content = $array;
			return;
		}

		$atts = get_object_vars( $this );
		foreach ( $array as $key => $value ) {
			if ( 0 === strpos( $key, $this->___ns_full ) )
				$key = substr( $key, strlen( $this->___ns_full ) + 1 );
			if ( is_null( $value ) || ( $this->___restrict && !array_key_exists( $key, $atts ) ) )
				continue;

			$this->$key = $value;
		}
	}
}

class PollDaddy_XML_Object extends Ghetto_XML_Object {
	var $___ns = 'pd';
	var $___ns_full = 'http://api.polldaddy.com/pdapi.xsd';
}

class PollDaddy_XML_Root extends PollDaddy_XML_Object {
	function xml( $prepend_ns = true, $pad = 0 ) {
		$xml = parent::xml( $prepend_ns, $pad );
		if ( !$pad ) {
			$pos = strpos( $xml, '>' );
			$xml = substr_replace( $xml, " xmlns:$this->___ns='$this->___ns_full'", $pos, 0 );
		}
		return $xml;
	}
}

class PollDaddy_Access extends PollDaddy_XML_Root {
	var $___name = 'pdAccess';

	var $_partnerGUID;
	var $_partnerUserID;

	var $demands;
}

class PollDaddy_Initiate extends PollDaddy_XML_Root {
	var $___cdata = array( 'Email', 'Password' );
	var $___name = 'pdInitiate';

	var $_partnerGUID;
	var $_partnerUserID;

	var $Email;
	var $Password;	
}

class PollDaddy_Request extends PollDaddy_XML_Root {
	var $___name = 'pdRequest';

	var $_partnerGUID;
	var $_version;
	var $_admin;

	var $userCode;
	var $demands;
}

class PollDaddy_Response extends PollDaddy_XML_Root {
	var $___name = 'pdResponse';

	var $_partnerGUID;
	var $_partnerUserID;

	var $userCode;
	var $demands;
	var $errors;
	var $queries;
}

class PollDaddy_Errors extends PollDaddy_XML_Object {
	var $___name = 'errors';

	var $error;
}

class PollDaddy_Error extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'error';

	var $___content;

	var $_id;
}

class PollDaddy_Queries extends PollDaddy_XML_Object {
	var $___name = 'queries';

	var $query;
}

class PollDaddy_Query extends PollDaddy_XML_Object {
	var $___cdata = array( 'text' );
	var $___name = 'query';

	var $_id;
	
	var $time;
	var $text;
	var $caller;
}

class PollDaddy_Demands extends PollDaddy_XML_Object {
	var $___name = 'demands';

	var $demand;
}

class PollDaddy_Demand extends PollDaddy_XML_Object {
	var $___name = 'demand';

	var $_id;

	var $account;
	var $poll;
	var $polls;
	var $emailAddress;
	var $message;
	var $list;
	var $search;
	var $result;
	var $comments; //need to add an request object for each new type 
	var $comment;
	var $extensions;
	var $folders;
	var $styles;
	var $style;
	var $packs;
	var $pack;
	var $languages;
	var $activity;
	var $rating_result;
	var $rating;
	var $nonce;
	var $partner;
}

class PollDaddy_Partner extends PollDaddy_XML_Object {
  var $___cdata = array( 'name' );
	var $___name = 'partner';

	var $_role;
	var $_users;
	
	var $name;
}

class PollDaddy_Account extends PollDaddy_XML_Object {
	var $___cdata = array( 'userName', 'email', 'password', 'firstName', 'lastName', 'websiteURL', 'avatarURL', 'bio' );
	var $___name = 'account';

	var $userName;
	var $email;
	var $password;
	var $firstName;
	var $lastName;
	var $countryCode;
	var $gender;
	var $yearOfBirth;
	var $websiteURL;
	var $avatarURL;
	var $bio;
}

class PollDaddy_List extends PollDaddy_XML_Object {
	var $___name = 'list';

	var $_start;
	var $_end;
	var $_id;
	
	var $period;
}

class PollDaddy_Polls extends PollDaddy_XML_Object {
	var $___name = 'polls';

	var $_total;

	var $poll;
}

class PollDaddy_Search extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'search';

	var $___content;

	var $poll;
}

class PollDaddy_Poll extends PollDaddy_XML_Object {
	var $___cdata = array( '___content', 'question', 'mediaCode', 'url' );
	var $___name = 'poll';

	var $___content;

	var $_id;
	var $_created;
	var $_responses;
	var $_folderID;
	var $_owner;
	var $_closed;

	var $question;
	var $multipleChoice;
	var $randomiseAnswers;
	var $otherAnswer;
	var $resultsType;
	var $blockRepeatVotersType;  
	var $blockExpiration;
	var $comments;
	var $makePublic;
	var $closePoll;
	var $closePollNow;
	var $closeDate;
	var $styleID;
	var $packID;
	var $folderID;
	var $languageID;
	var $parentID;
	var $keyword;
	var $sharing;
	var $rank;
	var $url;
	var $choices;
	var $mediaType; // new
	var $mediaCode; // new
	var $answers;
}

class PollDaddy_Poll_Result extends PollDaddy_XML_Object {
	var $___name = 'result';

	var $_id;

	var $answers;
	var $otherAnswers;
}

class PollDaddy_Poll_Answers extends PollDaddy_XML_Object {
	var $___name = 'answers';

	var $answer;
}

class PollDaddy_Poll_Answer extends PollDaddy_XML_Object {
	var $___cdata = array( '___content', 'text', 'mediaCode' );
	var $___name = 'answer';

	var $_id;
	var $_total;
	var $_percent;
	var $_mediaType; // old way
	var $_mediaCode;  // old way
	
	var $___content;
	
	var $text;	//removed ___content and replaced it with text node
	var $mediaType; // new
	var $mediaCode; // new
}

class PollDaddy_Other_Answers extends PollDaddy_XML_Object {
	var $___name = 'otherAnswers';

	var $otherAnswer;
}

class PollDaddy_Other_Answer extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'otherAnswer';

	var $___content;
}

class PollDaddy_Comments extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'comments';
	
	var $___content;

	var $_id;

	var $comment;
}

class PollDaddy_Comment extends PollDaddy_XML_Object {
	var $___cdata = array( 'name', 'email', 'text', 'url' );
	var $___name = 'comment';

	var $_id; //_ means variable corresponds to an attribute
	var $_method;
	var $_type;

	var $poll; // without _ means variable corresponds to an element
	var $name;
	var $email;
	var $text;
	var $url;
	var $date;
	var $ip;
}

class PollDaddy_Extensions extends PollDaddy_XML_Object {
	var $___name = 'extensions';
	
	var $folders;
	var $styles;
	var $packs;
	var $languages;
}

class PollDaddy_Folders extends PollDaddy_XML_Object {
	var $___name = 'folders';
	
	var $folder;
}

class PollDaddy_Folder extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'folder';
	
	var $___content;
	
	var $_id;
}

class PollDaddy_Styles extends PollDaddy_XML_Object {
	var $___name = 'styles';
	
	var $style;
}

class PollDaddy_Style extends PollDaddy_XML_Object {
	var $___cdata = array( 'title', 'css' );
	var $___name = 'style';
	
	var $_id;
	var $_type;
	var $_retro;
	
	var $title;
	var $date;	
	var $css;
}

class PollDaddy_Packs extends PollDaddy_XML_Object {
	var $___name = 'packs';
	
	var $pack;
}

class PollDaddy_Pack extends PollDaddy_XML_Object {
	var $___name = 'pack';
	
	var $_id;
	var $_date;
	var $_retro;
	
	var $pack;
}

class Custom_Pack extends PollDaddy_XML_Object {
	var $___name = 'pack';
	
	var $_type = 'user'; //type attribute is constant (for now)
	
	var $title;
	var $phrase;
	
	function xml( $prepend_ns = true, $pad = 0 ) {
		$xml = parent::xml( false, $pad );
		return $xml;
	}
}

class Custom_Pack_Phrase extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'phrase';
	
	var $___content;
	
	var $_phraseID;
	
	function xml( $prepend_ns = true, $pad = 0 ) {
		$xml = parent::xml( false, $pad );
		return $xml;
	}
}

class PollDaddy_Languages extends PollDaddy_XML_Object {
	var $___name = 'languages';
	
	var $language;
}

class PollDaddy_Language extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'language';
	
	var $___content;
	
	var $_id;
}

class PollDaddy_Activity extends PollDaddy_XML_Object {
	var $___cdata = array( '___content' );
	var $___name = 'activity';
	
	var $___content;
}

class PollDaddy_Nonce extends PollDaddy_XML_Object {
	var $___cdata = array( 'text', 'action' );
	var $___name = 'nonce';
	
	var $text;
	var $action;
	var $userCode;
}

class PollDaddy_Rating_Result extends PollDaddy_XML_Object {
	var $___name = 'rating_result';

	var $_id;
	
	var $ratings;
}

class PollDaddy_Ratings extends PollDaddy_XML_Object {
	var $___name = 'ratings';

	var $_total;
	var $rating;
}

class PollDaddy_Rating extends PollDaddy_XML_Object {
	var $___name = 'rating';
    	var $___cdata = array( 'settings', 'name', 'title', 'permalink' );

	var $_id;
	
	var $_type;
	var $_votes;
	var $uid;
	var $total1;
	var $total2;
	var $total3;
	var $total4;
	var $total5;
	var $average_rating;
	var $date;
	var $title;
	var $permalink;
	
	var $name;
	var $folder_id;
	var $settings;
}

class PollDaddy_Email extends PollDaddy_XML_Object {
	var $___cdata = array( 'custom' );
	var $___name = 'emailAddress';

	var $_id;
	var $_owner;

	var $folderID;
	var $address;
	var $firstname;
	var $lastname;
	var $custom;
	var $status;
}

class PollDaddy_Email_Message extends PollDaddy_XML_Object {
	var $___cdata = array( 'text' );
	var $___name = 'message';

	var $_id;
	var $_owner;

	var $text;
	var $groups;
}

class PollDaddy_XML_Parser {
	var $parser;
	var $polldaddy_objects = array(
		'http://api.polldaddy.com/pdapi.xsd:pdAccess' => 'PollDaddy_Access',
		'http://api.polldaddy.com/pdapi.xsd:pdInitiate' => 'PollDaddy_Initiate',
		'http://api.polldaddy.com/pdapi.xsd:pdRequest' => 'PollDaddy_Request',
		'http://api.polldaddy.com/pdapi.xsd:pdResponse' => 'PollDaddy_Response',
		'http://api.polldaddy.com/pdapi.xsd:errors' => 'PollDaddy_Errors',
		'http://api.polldaddy.com/pdapi.xsd:error' => 'PollDaddy_Error',
		'http://api.polldaddy.com/pdapi.xsd:demands' => 'PollDaddy_Demands',
		'http://api.polldaddy.com/pdapi.xsd:demand' => 'PollDaddy_Demand',
		'http://api.polldaddy.com/pdapi.xsd:queries' => 'PollDaddy_Queries',
		'http://api.polldaddy.com/pdapi.xsd:query' => 'PollDaddy_Query',
		'http://api.polldaddy.com/pdapi.xsd:account' => 'PollDaddy_Account',
		'http://api.polldaddy.com/pdapi.xsd:list' => 'PollDaddy_List',
		'http://api.polldaddy.com/pdapi.xsd:polls' => 'PollDaddy_Polls',
		'http://api.polldaddy.com/pdapi.xsd:search' => 'PollDaddy_Search',
		'http://api.polldaddy.com/pdapi.xsd:poll' => 'PollDaddy_Poll',
		'http://api.polldaddy.com/pdapi.xsd:emailAddress' => 'PollDaddy_Email',
		'http://api.polldaddy.com/pdapi.xsd:message' => 'PollDaddy_Email_Message',
		'http://api.polldaddy.com/pdapi.xsd:answers' => 'PollDaddy_Poll_Answers',
		'http://api.polldaddy.com/pdapi.xsd:answer' => 'PollDaddy_Poll_Answer',
		'http://api.polldaddy.com/pdapi.xsd:otherAnswers' => 'PollDaddy_Other_Answers',
		'http://api.polldaddy.com/pdapi.xsd:result' => 'PollDaddy_Poll_Result',
		'http://api.polldaddy.com/pdapi.xsd:comments' => 'PollDaddy_Comments',
		'http://api.polldaddy.com/pdapi.xsd:comment' => 'PollDaddy_Comment',
		'http://api.polldaddy.com/pdapi.xsd:extensions' => 'PollDaddy_Extensions',
		'http://api.polldaddy.com/pdapi.xsd:folders' => 'PollDaddy_Folders',
		'http://api.polldaddy.com/pdapi.xsd:folder' => 'PollDaddy_Folder',
		'http://api.polldaddy.com/pdapi.xsd:styles' => 'PollDaddy_Styles',
		'http://api.polldaddy.com/pdapi.xsd:style' => 'PollDaddy_Style',
		'http://api.polldaddy.com/pdapi.xsd:packs' => 'PollDaddy_Packs',
		'http://api.polldaddy.com/pdapi.xsd:pack' => 'PollDaddy_Pack',
		'http://api.polldaddy.com/pdapi.xsd:languages' => 'PollDaddy_Languages',
		'http://api.polldaddy.com/pdapi.xsd:language' => 'PollDaddy_Language',
		'http://api.polldaddy.com/pdapi.xsd:activity' => 'PollDaddy_Activity',
		'pack' => 'Custom_Pack',
		'phrase' => 'Custom_Pack_Phrase',
		'http://api.polldaddy.com/pdapi.xsd:rating_result' => 'PollDaddy_Rating_Result',
		'http://api.polldaddy.com/pdapi.xsd:ratings' => 'PollDaddy_Ratings',
		'http://api.polldaddy.com/pdapi.xsd:rating' => 'PollDaddy_Rating',
		'http://api.polldaddy.com/pdapi.xsd:nonce' => 'PollDaddy_Nonce',
		'http://api.polldaddy.com/pdapi.xsd:partner' => 'PollDaddy_Partner'
	);// the parser matches the tag names to the class name and creates an object defined by that class

	var $object_stack = array();
	var $object_pos = null;

	var $objects = array();

	function PollDaddy_XML_Parser( $xml = null ) {
		if ( is_null( $xml ) )
			return;

		return $this->parse( $xml );
	}

	function parse( $xml ) {
		$this->parser = xml_parser_create_ns( 'UTF-8' );
		xml_set_object( $this->parser, $this );
		xml_set_element_handler( $this->parser, 'tag_open', 'tag_close' );
		xml_set_character_data_handler( $this->parser, 'text' );
		xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $this->parser, XML_OPTION_SKIP_WHITE, 1 );

		xml_parse( $this->parser, $xml );
		xml_parser_free( $this->parser );
		return $this->objects;
	}

	function tag_open( &$parser, $tag, $attributes ) {
		$object_pos = $this->object_pos;
		if ( $this->object_stack ) {
			if ( isset( $this->object_stack[$object_pos]['args'][$tag] ) ) {
				if ( is_array( $this->object_stack[$object_pos]['args'][$tag] ) ) {
					$this->object_stack[$object_pos]['args'][$tag][] = false;
				} else {
					$this->object_stack[$object_pos]['args'][$tag] = array( $this->object_stack[$object_pos]['args'][$tag], false );
				}
				end( $this->object_stack[$object_pos]['args'][$tag] );
				$this->object_stack[$object_pos]['args_tag_pos'] = key( $this->object_stack[$object_pos]['args'][$tag] );
			} else {
				$this->object_stack[$object_pos]['args'][$tag] = false;
			}
			$this->object_stack[$object_pos]['args_tag'] = $tag;
		}

		if ( isset( $this->polldaddy_objects[$tag] ) ) {
			$this->object_stack[] = array(
				'tag' => $tag,
				'atts' => $attributes,
				'args' => array(),
				'parent' => $this->object_pos,
				'args_tag' => null,
				'args_tag_pos' => null
			);
			end( $this->object_stack );
			$this->object_pos = key( $this->object_stack );
		}
	}

	function text( &$parser, $text ) {
		if ( !$this->object_stack )
			return;

		$text = trim( $text );
		if ( !strlen( $text ) )
			return;

		if ( $this->object_stack[$this->object_pos]['args_tag_pos'] ) {
			if ( isset($this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']][$this->object_stack[$this->object_pos]['args_tag_pos']]) )
				$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']][$this->object_stack[$this->object_pos]['args_tag_pos']] .= $text;
			else
				$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']][$this->object_stack[$this->object_pos]['args_tag_pos']] = $text;
		} elseif ( $this->object_stack[$this->object_pos]['args_tag'] ) {
			if ( isset($this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']]) )
				$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']] .= $text;
			else
				$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']] = $text;
		} else {
			if ( isset($this->object_stack[$this->object_pos]['args']['___content']) )
				$this->object_stack[$this->object_pos]['args']['___content'] .= $text;
			else	
				$this->object_stack[$this->object_pos]['args']['___content'] = $text;
		}
	}

	function tag_close( &$parser, $tag ) {
		if ( isset( $this->polldaddy_objects[$tag] ) ) {
			if ( $tag !== $this->object_stack[$this->object_pos]['tag'] )
				die( 'damn' );

			$new = $this->polldaddy_objects[$tag];
			$new_object =& new $new( $this->object_stack[$this->object_pos]['args'], $this->object_stack[$this->object_pos]['atts'] );
                                                                                                                                
			if ( is_numeric( $this->object_stack[$this->object_pos]['parent'] ) ) {
				$this->object_pos = $this->object_stack[$this->object_pos]['parent'];
				if ( $this->object_stack[$this->object_pos]['args_tag_pos'] ) {
					$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']][$this->object_stack[$this->object_pos]['args_tag_pos']] =& $new_object;
				} elseif ( $this->object_stack[$this->object_pos]['args_tag'] ) {
					$this->object_stack[$this->object_pos]['args'][$this->object_stack[$this->object_pos]['args_tag']] =& $new_object;
				}
			} else {
				$this->object_pos = null;
				$this->objects[] =& $new_object;
			}

			array_pop( $this->object_stack );
		}
	}
}
?>
