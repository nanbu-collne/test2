jQuery(function ( $ ) {

	WC2Util = {
		checkAlp : function( argValue ) {
			if( argValue.match(/[^a-z|^A-Z]/g) ) {
				return false;
			}
			return true;
		},

		checkAlpNum : function( argValue ) {
			if( argValue.match(/[^0-9|^A-Z|^a-z]/g) ) {
				return false;
			}
			return true;
		},

		checkCode : function( argValue ) {
			if( argValue.match(/[^0-9|^a-z|^A-Z|^\-|^_]/g) ) {
				return false;
			}
			return true;
		},

		checkNum : function(argValue) {
			if( argValue.match(/[^0-9]/g) ) {
				return false;
			}
			return true;
		},

		checkNumMinus : function( argValue ) {
			if( argValue.match(/[^0-9|^\-|^\.]/g) ) {
				return false;
			}
			return true;
		},

		checkMoney : function( argValue ) {
			if( argValue.match(/[^0-9|^\.]/g) ) {
				return false;
			}
			return true;
		},

		checkPrice : function( argValue ) {
			if( argValue.match(/[^0-9|^\-|^\,|^\.]/g) ) {
				return false;
			}
			return true;
		},

		addComma : function( str ) {
			var strs = str.split('.');
			cnt = 0;
			n = "";
			m = "";
			if( strs[0].substr(0, 1) == "-" ) {
				m = "-";
				strs[0] = strs[0].substr(1);
			}
			for( i = strs[0].length-1; i >= 0; i-- ) {
				n = strs[0].charAt(i) + n;
				cnt++;
				if (((cnt % 3) == 0) && (i != 0)) n = ","+n;
			}
			n = m + n;
			if( undefined != strs[1] ) {
				res = n + '.' + strs[1];
			} else {
				res = n;
			}
			return res;
		}
	};
});



