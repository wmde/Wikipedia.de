<?php
define('SETUP_WIKI_BOXES', 1);
include_once("inc/config.inc.php");
include_once("inc/functions.inc.php");

header("Content-Type: text/html; charset=UTF-8");

// Cookie setzen (zum Test)
if (!isset($_COOKIE) || !count($_COOKIE)) {
	setcookie( "cookies" , true);
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0" />
    <title>Wikipedia, die freie Enzyklop&auml;die</title>
    <link rel="apple-touch-icon" href="/img/wikipedia.png" />
    <link rel="stylesheet" media="screen" type="text/css" href="style.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="blackout.css" />
    <script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.cookie.min.js"></script>
    <script language="JavaScript" type="text/javascript" src="suggest.js"></script>
</head>

<body>
<div id="WMDE-Banner-Container"></div>
<div id="main">
    <div id="ceb-inline" class="ceb ceb-styles">
        <div class="ceb-block">
            <div class="ceb-inline-main">
                <span class="ceb-inline-wordmark"><img src="https://upload.wikimedia.org/wikipedia/commons/e/ed/Wikipedia-logo-white.svg" width="160px" height="32.5px"></span>
                <span class="ceb-inline-title">
                    <span class="redtitle">DIES IST UNSERE <span class="highlight">LETZTE CHANCE</SPAN>. HELFEN SIE UNS, DAS URHEBERRECHT IN EUROPA ZU MODERNISIEREN.</span>
                </span>
                <span class="ceb-inline-message">
                    <p>Liebe Besucherin, lieber Besucher,</p>
                    <p>warum können Sie Wikipedia nicht wie gewohnt benutzen? Die Autorinnen und Autoren der Wikipedia haben sich entschieden, Wikipedia heute aus Protest gegen Teile der geplanten EU-Urheberrechtsreform abzuschalten. Dieses Gesetz soll am 26. März vom Parlament der Europäischen Union verabschiedet werden.</p>
                    <p>Die geplante Reform könnte dazu führen, dass das freie Internet erheblich eingeschränkt wird. Selbst kleinste Internetplattformen müssten Urheberrechtsverletzungen ihrer Userinnen und User präventiv unterbinden (Artikel 13 des geplanten Gesetzes), was in der Praxis nur mittels fehler- und missbrauchsanfälliger Upload-Filter umsetzbar wäre. Zudem müssten alle Webseiten für kurze Textausschnitte aus Presseerzeugnissen Lizenzen erwerben, um ein neu einzuführendes Verleger-Recht einzuhalten (Artikel 11). Beides zusammen könnte die Meinungs-, Kunst- und Pressefreiheit erheblich beeinträchtigen.</p>
                    <p>Obwohl zumindest Wikipedia ausdrücklich von Artikel 13 der neuen Urheberrechtsrichtlinie ausgenommen ist (allerdings nicht von Artikel 11), wird das Freie Wissen selbst dann leiden, wenn Wikipedia eine Oase in der gefilterten Wüste des Internets bleibt.</p>
                    <p>Gegen die Reform in ihrer gegenwärtigen Fassung protestieren auch rund <a href="https://www.change.org/p/stoppt-die-zensurmaschine-rettet-das-internet-uploadfilter-artikel13-saveyourinternet">fünf Millionen Menschen in einer Petition</a>, <a href="https://copybuzz.com/wp-content/uploads/2018/07/Copyright-Open-Letter-on-EP-Plenary-Vote-on-Negotiation-Mandate.pdf">145 Bürgerrechts- und Menschenrechtsorganisationen</a>, Wirtschafts- und IT-Verbände (darunter <a href="https://www.bitkom.org/Presse/Presseinformation/Bitkom-zur-Abstimmung-ueber-die-EU-Urheberrechtsreform.html">Bitkom</a>, der deutsche <a href="https://deutschestartups.org/presse/news/eu-urheberrechtsreform-angenommen-startup-verband-beklagt-immensen-schaden-fuer-europaeische-startups/">Start-Up-Verband</a> oder der <a href="https://www.ccc.de/de/updates/2018/europaweite-upload-filter-starken-nur-die-macht-von-google-und-facebook">Chaos-Computer-Club</a>), <a href="https://www.eff.org/deeplinks/2018/06/internet-luminaries-ring-alarm-eu-copyright-filtering-proposal">Internet-Pioniere wie Tim Berners-Lee</a>, <a href="https://www.freischreiber.de/aktuelle/diese-reform-bringt-uns-freie-urheber-keinen-schritt-weiter/">Journalistenverbände</a> sowie <a href="http://www.spiegel.de/netzwelt/netzpolitik/eu-urheberrechtsreform-youtuber-wollen-gegen-artikel-13-auf-die-strasse-a-1253001.html">Kreativschaffende</a>.</p>
                    <p>Wir bitten Sie deshalb darum, die Abgeordneten des Europäischen Parlaments zu kontaktieren und sie über Ihre Haltung zur geplanten Reform zu informieren.</p>
                    <p>Danke.</p>
                </span>
                <span class="ceb-inline-buttons">
                    <a class="ceb-inline-link" href="http://www.europarl.europa.eu/meps/de/home" title="EU Copyright Reform" target="_blank">
                    <span class="ceb-btn-submit ceb-btn-table">
                        <span class="ceb-btn-cell ceb-btn-txt">
                            KONTAKTIEREN SIE IHRE ABGEORDNETEN ➝
                        </span>
                    </span>
                    </a>
                </span>
                <span class="ceb-inline-smalltext">
                   <p>
                       Der oben verlinkte Abgeordneten-Suchdienst des Europäischen Parlaments wird nicht durch Wikimedia Deutschland betrieben; Ihre Nutzung der dortigen Website unterliegt den dortigen <a style="color:#999" href="http://www.europarl.europa.eu/portal/de/legal-notice">Nutzungsbedingungen</a>.
                   </p>
                </span>
            </div>
            <div class="ceb-inline-footer">
                <div class="ceb-inline-communitytextlogo">DEUTSCHSPRACHIGE<br>WIKIPEDIA-COMMUNITY</div>
                <div class="ceb-inline-clock"><div class="container" style="width:50px; display: table-cell;"></div><span style="display:table-cell;color: #999; vertical-align: middle; line-height: 1em; text-align:left;">STUNDEN<br>VERBLEIBEN<br></span></div>
            </div>
        </div>
    </div>
    <footer class="page-footer" id="donationfooter">
        <div class="link-block">
            <ul>
                <li><a onclick="triggerPiwikTrack(this, 'wikimedia.de-logo');" href="https://www.wikimedia.de"><img class="wikimedia-logo" src="img/wmde_logo_white.svg" alt="Wikimedia Deutschland e.V."></a></li>
                <li>
                    <p><strong>Wikimedia Deutschland e. V.</strong></p>
                    <p><a href="https://www.wikimedia.de/de/ueber-uns">Über uns</a></p>
                    <p><a href="https://wikimedia-deutschland.softgarden.io/de/vacancies">Stellenangebote</a></p>
                    <p><a href="https://www.wikimedia.de/de/impressum">Impressum & Kontakt</a></p>
                </li>
                <li>
                    <p><strong>Mitwirken</strong></p>
                    <p><a href="https://spenden.wikimedia.de/apply-for-membership?piwik_campaign=wpdefooter&piwik_kwd=wpdefooterbtn">Mitglied werden</a></p>
                    <p><a href="https://spenden.wikimedia.de/?piwik_campaign=wpdefooter&piwik_kwd=wpdefooterbtn">Jetzt spenden</a></p>
                    <p><a href="https://spenden.wikimedia.de/use-of-funds?piwik_campaign=wpdefooter&piwik_kwd=wpdefooterbtn">Mittelverwendung</a></p>
                </li>
                <li>
                    <p><strong>Vereinskanäle</strong></p>
                    <p><a href="https://blog.wikimedia.de/">Unser Blog</a></p>
                    <p><a href="https://www.facebook.com/WMDEeV">Facebook</a></p>
                    <p><a href="https://twitter.com/wikimediade">Twitter</a></p>
                </li>
            </ul>
        </div>
    </footer>
</div>

<!-- temporary tracking of page views with donation tracker -->
<img src="" id="piwik-tracking"/>
<script type="text/javascript">
	if( Math.random() <= 0.01 ) {
		var pwkUrl = location.protocol + "//tracking.wikimedia.de/piwik.php?idsite=3&rec=1&url=",
			trackUrl = "https://wikipedia.de/";
		$( '#piwik-tracking' ).attr( 'src', pwkUrl + encodeURIComponent( trackUrl ) );
	}
</script>
<script type="application/javascript" src="https://bruce.wikipedia.de/banners/wikipedia.de-banners/stats.js"></script>
<?php
$randomBanner = 'your-contribution-to-free-knowledge.js';
$rawUrlBanner = filter_input( INPUT_GET, 'banner', FILTER_UNSAFE_RAW );
$filteredUrlBanner = basename( filter_input(
	INPUT_GET,
	'banner',
	FILTER_SANITIZE_SPECIAL_CHARS,
	FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
) );
$urlBanner = ( $filteredUrlBanner && $rawUrlBanner === $filteredUrlBanner ) ? sprintf( 'banners/wikipedia.de-banners/%s.js', $filteredUrlBanner) : $randomBanner;
?>

<script>
	"use strict";

	function _instanceof(left, right) { if (right != null && typeof Symbol !== "undefined" && right[Symbol.hasInstance]) { return right[Symbol.hasInstance](left); } else { return left instanceof right; } }

	function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

	function _classCallCheck(instance, Constructor) { if (!_instanceof(instance, Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

	function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

	var Countdown =
		/*#__PURE__*/
		function () {
			_createClass(Countdown, [{
				key: "TIMESTAMP_SECOND",
				get: function get() {
					return 1000;
				}
			}, {
				key: "TIMESTAMP_MINUTE",
				get: function get() {
					return 60 * this.TIMESTAMP_SECOND;
				}
			}, {
				key: "TIMESTAMP_HOUR",
				get: function get() {
					return 60 * this.TIMESTAMP_MINUTE;
				}
			}, {
				key: "TIMESTAMP_DAY",
				get: function get() {
					return 24 * this.TIMESTAMP_HOUR;
				}
			}, {
				key: "TIMESTAMP_WEEK",
				get: function get() {
					return 7 * this.TIMESTAMP_DAY;
				}
			}, {
				key: "TIMESTAMP_YEAR",
				get: function get() {
					return 365 * this.TIMESTAMP_DAY;
				}
				/**
				 * @param {{}} userOptions structure like this.options below
				 */

			}]);

			function Countdown(userOptions) {
				_classCallCheck(this, Countdown);

				this.options = {
					cont: null,
					countdown: true,
					date: {
						year: 0,
						month: 0,
						day: 0,
						hour: 0,
						minute: 0,
						second: 0
					},
					endCallback: null,
					outputFormat: 'year|week|day|hour|minute|second',
					outputTranslation: {
						year: 'Years',
						week: 'Weeks',
						day: 'Days',
						hour: 'Hours',
						minute: 'Minutes',
						second: 'Seconds'
					}
				};
				this.lastTick = null;
				this.intervalsBySize = ['year', 'week', 'day', 'hour', 'minute', 'second'];
				this.elementClassPrefix = 'countDown_';
				this.interval = null;
				this.digitConts = {};

				this._assignOptions(this.options, userOptions);
			}

			_createClass(Countdown, [{
				key: "start",
				value: function start() {
					var _this = this;

					var date, dateData;

					this._fixCompatibility();

					date = this._getDate(this.options.date);
					dateData = this._prepareTimeByOutputFormat(date);

					this._writeData(dateData);

					this.lastTick = dateData;

					if (this.options.countdown && date.getTime() <= Date.now()) {
						if (typeof this.options.endCallback === 'function') {
							this.stop();
							this.options.endCallback();
						}
					} else {
						this.interval = setInterval(function () {
							_this._updateView(_this._prepareTimeByOutputFormat(date));
						}, this.TIMESTAMP_SECOND);
					}
				}
			}, {
				key: "stop",
				value: function stop() {
					if (this.interval !== null) {
						clearInterval(this.interval);
					}
				}
				/**
				 * @param {Date|Object|String|Number} date
				 *
				 * @returns {Date}
				 * @private
				 */

			}, {
				key: "_getDate",
				value: function _getDate(date) {
					if (_typeof(date) === 'object') {
						if (_instanceof(date, Date)) {
							return date;
						} else {
							var expectedValues = {
								day: 0,
								month: 0,
								year: 0,
								hour: 0,
								minute: 0,
								second: 0
							};

							for (var i in expectedValues) {
								if (expectedValues.hasOwnProperty(i) && date.hasOwnProperty(i)) {
									expectedValues[i] = date[i];
								}
							}

							return new Date(expectedValues.year, expectedValues.month > 0 ? expectedValues.month - 1 : expectedValues.month, expectedValues.day, expectedValues.hour, expectedValues.minute, expectedValues.second);
						}
					} else if (typeof date === 'number' || typeof date === 'string') {
						return new Date(date);
					} else {
						return new Date();
					}
				}
				/**
				 * @param {Date} dateObj
				 *
				 * @return {{}}
				 * @private
				 */

			}, {
				key: "_prepareTimeByOutputFormat",
				value: function _prepareTimeByOutputFormat(dateObj) {
					var _this2 = this;

					var usedIntervals,
						output = {},
						timeDiff;
					usedIntervals = this.intervalsBySize.filter(function (item) {
						return _this2.options.outputFormat.split('|').indexOf(item) !== -1;
					});
					timeDiff = this.options.countdown ? dateObj.getTime() - Date.now() : Date.now() - dateObj.getTime();
					usedIntervals.forEach(function (item) {
						var value;

						if (timeDiff > 0) {
							switch (item) {
								case 'year':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_YEAR);
									timeDiff -= value * _this2.TIMESTAMP_YEAR;
									break;

								case 'week':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_WEEK);
									timeDiff -= value * _this2.TIMESTAMP_WEEK;
									break;

								case 'day':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_DAY);
									timeDiff -= value * _this2.TIMESTAMP_DAY;
									break;

								case 'hour':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_HOUR);
									timeDiff -= value * _this2.TIMESTAMP_HOUR;
									break;

								case 'minute':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_MINUTE);
									timeDiff -= value * _this2.TIMESTAMP_MINUTE;
									break;

								case 'second':
									value = Math.trunc(timeDiff / _this2.TIMESTAMP_SECOND);
									timeDiff -= value * _this2.TIMESTAMP_SECOND;
									break;
							}
						} else {
							value = '00';
						}

						output[item] = (('' + value).length < 2 ? '0' + value : '' + value).split('');
					});
					return output;
				}
			}, {
				key: "_fixCompatibility",
				value: function _fixCompatibility() {
					Math.trunc = Math.trunc || function (x) {
						if (isNaN(x)) {
							return NaN;
						}

						if (x > 0) {
							return Math.floor(x);
						}

						return Math.ceil(x);
					};
				}
				/**
				 * @param {{}} data
				 * @private
				 */

			}, {
				key: "_writeData",
				value: function _writeData(data) {
					var _this3 = this;

					var code = "<div class=\"".concat(this.elementClassPrefix, "cont\">"),
						intervalName;

					for (intervalName in data) {
						if (data.hasOwnProperty(intervalName)) {
							var element = "<div class=\"".concat(this.elementClassPrefix, "_interval_basic_cont\">\n                                       <div class=\"").concat(this._getIntervalContCommonClassName(), " ").concat(this._getIntervalContClassName(intervalName), "\">"),
								intervalDescription = "<div class=\"".concat(this.elementClassPrefix, "interval_basic_cont_description\">\n                                                   ").concat(this.options.outputTranslation[intervalName], "\n                                               </div>");
							data[intervalName].forEach(function (digit, index) {
								element += "<div class=\"".concat(_this3._getDigitContCommonClassName(), " ").concat(_this3._getDigitContClassName(index), "\">\n                                        ").concat(_this3._getDigitElementString(digit, 0), "\n                                    </div>");
							});
							code += element + '</div>' + intervalDescription + '</div>';
						}
					}

					this.options.cont.innerHTML = code + '</div>';
					this.lastTick = data;
				}
				/**
				 * @param {Number} newDigit
				 * @param {Number} lastDigit
				 *
				 * @returns {String}
				 * @private
				 */

			}, {
				key: "_getDigitElementString",
				value: function _getDigitElementString(newDigit, lastDigit) {
					return "<div class=\"".concat(this.elementClassPrefix, "digit_last_placeholder\">\n                        <div class=\"").concat(this.elementClassPrefix, "digit_last_placeholder_inner\">\n                            ").concat(lastDigit, "\n                        </div>\n                    </div>\n                    <div class=\"").concat(this.elementClassPrefix, "digit_new_placeholder\">").concat(newDigit, "</div>\n                    <div class=\"").concat(this.elementClassPrefix, "digit_last_rotate\">").concat(lastDigit, "</div>\n                    <div class=\"").concat(this.elementClassPrefix, "digit_new_rotate\">\n                        <div class=\"").concat(this.elementClassPrefix, "digit_new_rotated\">\n                            <div class=\"").concat(this.elementClassPrefix, "digit_new_rotated_inner\">\n                                ").concat(newDigit, "\n                            </div>\n                        </div>\n                    </div>");
				}
				/**
				 * @param {{}} data
				 * @private
				 */

			}, {
				key: "_updateView",
				value: function _updateView(data) {
					var _this4 = this;

					var _loop = function _loop(intervalName) {
						if (data.hasOwnProperty(intervalName)) {
							data[intervalName].forEach(function (digit, index) {
								if (_this4.lastTick !== null && _this4.lastTick[intervalName][index] !== data[intervalName][index]) {
									_this4._getDigitCont(intervalName, index).innerHTML = _this4._getDigitElementString(data[intervalName][index], _this4.lastTick[intervalName][index]);
								}
							});
						}
					};

					for (var intervalName in data) {
						_loop(intervalName);
					}

					this.lastTick = data;
				}
				/**
				 * @param {String} intervalName
				 * @param {String} index
				 *
				 * @returns {HTMLElement}
				 * @private
				 */

			}, {
				key: "_getDigitCont",
				value: function _getDigitCont(intervalName, index) {
					if (!this.digitConts["".concat(intervalName, "_").concat(index)]) {
						this.digitConts["".concat(intervalName, "_").concat(index)] = this.options.cont.querySelector(".".concat(this._getIntervalContClassName(intervalName), " .").concat(this._getDigitContClassName(index)));
					}

					return this.digitConts["".concat(intervalName, "_").concat(index)];
				}
				/**
				 * @param {String} intervalName
				 *
				 * @returns {String}
				 * @private
				 */

			}, {
				key: "_getIntervalContClassName",
				value: function _getIntervalContClassName(intervalName) {
					return "".concat(this.elementClassPrefix, "interval_cont_").concat(intervalName);
				}
				/**
				 * @returns {String}
				 * @private
				 */

			}, {
				key: "_getIntervalContCommonClassName",
				value: function _getIntervalContCommonClassName() {
					return "".concat(this.elementClassPrefix, "interval_cont");
				}
				/**
				 * @param {String} index
				 *
				 * @returns {String}
				 * @private
				 */

			}, {
				key: "_getDigitContClassName",
				value: function _getDigitContClassName(index) {
					return "".concat(this.elementClassPrefix, "digit_cont_").concat(index);
				}
				/**
				 * @returns {String}
				 * @private
				 */

			}, {
				key: "_getDigitContCommonClassName",
				value: function _getDigitContCommonClassName() {
					return "".concat(this.elementClassPrefix, "digit_cont");
				}
				/**
				 * @param {{}} options
				 * @param {{}} userOptions
				 */

			}, {
				key: "_assignOptions",
				value: function _assignOptions(options, userOptions) {
					for (var i in options) {
						if (options.hasOwnProperty(i) && userOptions.hasOwnProperty(i)) {
							if (options[i] !== null && _typeof(options[i]) === 'object' && _typeof(userOptions[i]) === 'object') {
								this._assignOptions(options[i], userOptions[i]);
							} else {
								options[i] = userOptions[i];
							}
						}
					}
				}
			}]);

			return Countdown;
		}();
</script>

<script>
	let cd = new Countdown({
		cont: document.querySelector('.container'),
		countdown: true, // true for classic countdown, false for countup
		date: {
			day: 22,
			month: 3,
			year: 2019,
			hour: 0,
			minute: 59,
			second: 59,
		},
		outputTranslation: {
			year: '',
			week: '',
			day: '',
			hour: '',
			minute: '',
			second: ''
		},

		endCallback: null,
		outputFormat: 'hour',
	});

	cd.start();
</script>

<script>
	var ceb = ceb || {};

	ceb.dayNames = {
		'en' : [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ],
	};
</script>


<script type="application/javascript" src="https://bruce.wikipedia.de/<?php echo $urlBanner; ?>"></script>

<!-- Matomo -->
<script async defer type="text/javascript" src="tracking.js"></script>
<noscript><p><img src="//stats.wikimedia.de/piwik.php?idsite=3&amp;rec=1" style="border:0;" alt=""/></p></noscript>
<!-- End Matomo Code -->

</body>
</html>
