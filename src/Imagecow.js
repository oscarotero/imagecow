/**
 * Imagecow JS/PHP library
 *
 * Check the client device properties (width, height, connection) and save a cookie with the data to generate responsive images
 */

window.Imagecow = {
	cookie_seconds: 3600*24,
	cookie_name: 'Imagecow_detection',
	cookie_path: '/',

	init: function () {
		var dimensions = this.getClientDimensions();
		var speed = this.getConnectionSpeed();
		var value = dimensions + ',' + speed;

		if (value != this.getCookie()) {
			this.setCookie(value);
		}
	},

	getClientDimensions: function () {
		if (typeof window.innerWidth != 'undefined') {
			return window.innerWidth + ',' + window.innerHeight;
		} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			return document.documentElement.clientWidth + ',' + document.documentElement.clientHeight;
		} else {
			return document.getElementsByTagName('body')[0].clientWidth + ',' + document.getElementsByTagName('body')[0].clientHeight;
		}
	},

	getConnectionSpeed: function () {
		var connection = navigator.connection || {"type": 0};

		switch (connection.type) {
			case connection.CELL_3G:
			case connection.CELL_2G:
				return 'slow';
		}

		return 'fast';
	},

	setCookie: function (value) {
		var date = new Date();
		date.setTime(date.getTime()+(this.cookie_seconds*1000));
		var expires = "; expires="+date.toGMTString();

		document.cookie = this.cookie_name+"="+value+expires+"; path="+this.cookie_path;
	},

	getCookie: function () {
		var nameEQ = this.cookie_name + "=";
		var ca = document.cookie.split(';');

		for (var i=0;i < ca.length;i++) {
			var c = ca[i];

			while (c.charAt(0)==' ') {
				c = c.substring(1,c.length);
			}

			if (c.indexOf(nameEQ) == 0) {
				return c.substring(nameEQ.length,c.length);
			}
		}

		return null;
	},

	deleteCookie: function () {
		var date = new Date();
		date.setTime(date.getTime()-1000);
		var expires = "; expires="+date.toGMTString();

		document.cookie = this.cookie_name+"="+expires+"; path="+this.cookie_path;
	}
}