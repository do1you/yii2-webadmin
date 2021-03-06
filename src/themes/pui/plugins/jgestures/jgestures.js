/**
 @Name：jgestures V0.1
 @Author：统一 54901801@qq.com
 @Date：2016-7-21 
 */
define('pui/jgestures',['jquery'],function(require,exports,moudles){
	(function($){
		$.jGestures = {};
		$.jGestures.defaults = {};
		$.jGestures.defaults.thresholdShake =  {
			requiredShakes : 10,
			freezeShakes: 100,
			frontback : {
				sensitivity: 10
			 },
			leftright : {
				sensitivity: 10
			},
			updown : {
				sensitivity: 10
			}
		};

		$.jGestures.defaults.thresholdPinchopen = 0.05;
		$.jGestures.defaults.thresholdPinchmove = 0.05;
		$.jGestures.defaults.thresholdPinch = 0.05;
		$.jGestures.defaults.thresholdPinchclose = 0.05;
		$.jGestures.defaults.thresholdRotatecw = 5; //deg
		$.jGestures.defaults.thresholdRotateccw = 5; // deg
		$.jGestures.defaults.thresholdMove = 20;
		$.jGestures.defaults.thresholdSwipe = 100;
		$.jGestures.data = {};
		$.jGestures.data.capableDevicesInUserAgentString = ['iPad','iPhone','iPod','Mobile Safari'];
		$.jGestures.data.hasGestures = (function () { var _i; for(_i = 0; _i < $.jGestures.data.capableDevicesInUserAgentString.length; _i++ ) {  if (navigator.userAgent.indexOf($.jGestures.data.capableDevicesInUserAgentString[_i]) !== -1 ) {return true;} } return false; } )();
		$.hasGestures = $.jGestures.data.hasGestures;
		$.jGestures.events = {
			touchstart : 'jGestures.touchstart',
			touchendStart: 'jGestures.touchend;start',
			touchendProcessed: 'jGestures.touchend;processed',
			gesturestart: 'jGestures.gesturestart',
			gestureendStart: 'jGestures.gestureend;start',
			gestureendProcessed: 'jGestures.gestureend;processed'
		};

		$.each({
			orientationchange_orientationchange01: "orientationchange",
			// event: gestures
			gestureend_pinchopen01: "pinchopen",
			gestureend_pinchclose01: "pinchclose",
			gestureend_rotatecw01 : 'rotatecw',
			gestureend_rotateccw01 : 'rotateccw',
			// move events
			gesturechange_pinch01: 'pinch',
			gesturechange_rotate01: 'rotate',
			touchstart_swipe13: 'swipemove',
			// event: touches
			touchstart_swipe01: "swipeone",
			touchstart_swipe02: "swipetwo",
			touchstart_swipe03: "swipethree",
			touchstart_swipe04: "swipefour",
			touchstart_swipe05: 'swipeup',
			touchstart_swipe06: 'swiperightup',
			touchstart_swipe07: 'swiperight',
			touchstart_swipe08: 'swiperightdown',
			touchstart_swipe09: 'swipedown',
			touchstart_swipe10: 'swipeleftdown',
			touchstart_swipe11: 'swipeleft',
			touchstart_swipe12: 'swipeleftup',
			touchstart_tap01: 'tapone',
			touchstart_tap02: 'taptwo',
			touchstart_tap03: 'tapthree',
			touchstart_tap04: 'tapfour',

			devicemotion_shake01: 'shake',
			devicemotion_shake02: 'shakefrontback',
			devicemotion_shake03: 'shakeleftright',
			devicemotion_shake04: 'shakeupdown'

		},

		function( sInternal_, sPublicFN_ ) {
			$.event.special[ sPublicFN_ ] = {
				setup: function () {
					var _aSplit = sInternal_.split('_');
					var _sDOMEvent = _aSplit[0]; //
					var _sGestureEvent = _aSplit[1].slice(0,_aSplit[1].length-2);
					var _$element = $(this);
					var _oDatajQueryGestures ;
					var oObj;
					if (!_$element.data('ojQueryGestures') || !_$element.data('ojQueryGestures')[_sDOMEvent])  {
						_oDatajQueryGestures = _$element.data('ojQueryGestures') || {};
						oObj = {};
						oObj[_sDOMEvent] = true;
						$.extend(true,_oDatajQueryGestures,oObj);
						_$element.data('ojQueryGestures' ,_oDatajQueryGestures);

						if($.hasGestures) {
							switch(_sGestureEvent) {
								case 'orientationchange':
									_$element.get(0).addEventListener('orientationchange', _onOrientationchange, false);
								break;
								case 'shake':
								case 'shakefrontback':
								case 'shakeleftright':
								case 'shakeupdown':
								case 'tilt':
									_$element.get(0).addEventListener('devicemotion', _onDevicemotion, false);
								break;
								case 'tap':
								case 'swipe':
								case 'swipeup':
								case 'swiperightup':
								case 'swiperight':
								case 'swiperightdown':
								case 'swipedown':
								case 'swipeleftdown':
								case 'swipeleft':
									_$element.get(0).addEventListener('touchstart', _onTouchstart, false);
								break;
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
									_$element.get(0).addEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).addEventListener('gestureend', _onGestureend, false);
								break;
								case 'pinch':
								case 'rotate':
									_$element.get(0).addEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).addEventListener('gesturechange', _onGesturechange, false);
								break;
							}
						}
						else {
							switch(_sGestureEvent) {
								case 'tap':
								case 'swipe':
									 _$element.bind('mousedown', _onTouchstart);
								break;
								case 'orientationchange':
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
								case 'pinch':
								case 'rotate':
								case 'shake':
								case 'tilt':

								break;
							}
						}

					}
					return false;
				},

				add : function(event_) {
					var _$element = $(this);
					var _oDatajQueryGestures = _$element.data('ojQueryGestures');
					_oDatajQueryGestures[event_.type] = { 'originalType' : event_.type } ;
					return false;
				},

				remove : function(event_) {
					var _$element = $(this);
					var _oDatajQueryGestures = _$element.data('ojQueryGestures');
					_oDatajQueryGestures[event_.type] = false;
					_$element.data('ojQueryGestures' ,_oDatajQueryGestures );
					return false;
				},

				teardown : function() {
					var _aSplit = sInternal_.split('_');
					var _sDOMEvent = _aSplit[0]; //
					var _sGestureEvent = _aSplit[1].slice(0,_aSplit[1].length-2);
					var _$element = $(this);
					var _oDatajQueryGestures;
					var oObj;
					if (!_$element.data('ojQueryGestures') || !_$element.data('ojQueryGestures')[_sDOMEvent])  {
						_oDatajQueryGestures = _$element.data('ojQueryGestures') || {};
						oObj = {};
						oObj[_sDOMEvent] = false;
						$.extend(true,_oDatajQueryGestures,oObj);
						_$element.data('ojQueryGestures' ,_oDatajQueryGestures);
						if($.hasGestures) {
							switch(_sGestureEvent) {
								case 'orientationchange':
									_$element.get(0).removeEventListener('orientationchange', _onOrientationchange, false);
								break;

								case 'shake':
								case 'shakefrontback':
								case 'shakeleftright':
								case 'shakeupdown':
								case 'tilt':
									_$element.get(0).removeEventListener('devicemotion', _onDevicemotion, false);
								break;
								case 'tap':
								case 'swipe':
								case 'swipeup':
								case 'swiperightup':
								case 'swiperight':
								case 'swiperightdown':
								case 'swipedown':
								case 'swipeleftdown':
								case 'swipeleft':
								case 'swipeleftup':
									_$element.get(0).removeEventListener('touchstart', _onTouchstart, false);
									_$element.get(0).removeEventListener('touchmove', _onTouchmove, false);
									_$element.get(0).removeEventListener('touchend', _onTouchend, false);
								break;
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
									_$element.get(0).removeEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).removeEventListener('gestureend', _onGestureend, false);
								break;
								case 'pinch':
								case 'rotate':
									_$element.get(0).removeEventListener('gesturestart', _onGesturestart, false);
									_$element.get(0).removeEventListener('gesturechange', _onGesturechange, false);
								break;
							}
						}
						else {
							switch(_sGestureEvent) {
								case 'tap':
								case 'swipe':
									_$element.unbind('mousedown', _onTouchstart);
									_$element.unbind('mousemove', _onTouchmove);
									_$element.unbind('mouseup', _onTouchend);
								break;
								case 'orientationchange':
								case 'pinchopen':
								case 'pinchclose' :
								case 'rotatecw' :
								case 'rotateccw' :
								case 'pinch':
								case 'rotate':
								case 'shake':
								case 'tilt':

								break;
							}
						}

					}
				return false;
				}

			};
		});

		function _createOptions(oOptions_) {
			oOptions_.startMove = (oOptions_.startMove) ? oOptions_.startMove : {startX: null,startY:null,timestamp:null}  ;
			var _iNow = new Date().getTime();
			var _oDirection;
			var _oDelta;
			if (oOptions_.touches) {
				_oDelta = [
					{
						lastX: oOptions_.deltaX ,
						lastY: oOptions_.deltaY,
						moved: null,
						startX:  oOptions_.screenX - oOptions_.startMove.screenX ,
						startY: oOptions_.screenY - oOptions_.startMove.screenY
					}
				];

				_oDirection =  {
					vector: oOptions_.vector || null,
					orientation : window.orientation || null,
					lastX : ((_oDelta[0].lastX > 0) ? +1 : ( (_oDelta[0].lastX < 0) ? -1 : 0 ) ),
					lastY : ((_oDelta[0].lastY > 0) ? +1 : ( (_oDelta[0].lastY < 0) ? -1 : 0 ) ),
					startX : ((_oDelta[0].startX > 0) ? +1 : ( (_oDelta[0].startX < 0) ? -1 : 0 ) ),
					startY : ((_oDelta[0].startY > 0) ? +1 : ( (_oDelta[0].startY < 0) ? -1 : 0 ) )
				};

				_oDelta[0].moved =  Math.sqrt(Math.pow(Math.abs(_oDelta[0].startX), 2) + Math.pow(Math.abs(_oDelta[0].startY), 2));

			}
			return {
				type: oOptions_.type || null,
				originalEvent: oOptions_.event || null,
				delta : _oDelta  || null,
				direction : _oDirection || { orientation : window.orientation || null, vector: oOptions_.vector || null},
				duration: (oOptions_.duration) ? oOptions_.duration : ( oOptions_.startMove.timestamp ) ? _iNow - oOptions_.timestamp : null,
				rotation: oOptions_.rotation || null,
				scale: oOptions_.scale || null,
				description : oOptions_.description || [
					oOptions_.type,
					':',
					oOptions_.touches,
					':',
					((_oDelta[0].lastX != 0) ? ((_oDelta[0].lastX > 0) ? 'right' : 'left') : 'steady'),
					':',
					((_oDelta[0].lastY != 0) ? ( (_oDelta[0].lastY > 0) ? 'down' : 'up') :'steady')
					].join('')
			};

		}

		function _onOrientationchange(event_) {
			var _aDict = ['landscape:clockwise:','portrait:default:','landscape:counterclockwise:','portrait:upsidedown:'];
			$(window).triggerHandler('orientationchange',
				{
					direction : {orientation: window.orientation},
					description : [
						'orientationchange:',
						_aDict[( (window.orientation / 90) +1)],
						window.orientation
						].join('')
				});
		}

		function _onDevicemotion(event_) {

			var _sType;
			var _$element = $(window);
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _oThreshold = $.jGestures.defaults.thresholdShake;
			var _oLastDevicePosition = _oDatajQueryGestures.oDeviceMotionLastDevicePosition || {
				accelerationIncludingGravity : {
					x: 0,
					y: 0,
					z: 0
				},
				shake : {
					eventCount: 0,
					intervalsPassed: 0,
					intervalsFreeze: 0
				},
				shakeleftright : {
					eventCount: 0,
					intervalsPassed: 0,
					intervalsFreeze: 0
				},
				shakefrontback : {
					eventCount: 0,
					intervalsPassed: 0,
					intervalsFreeze: 0
				},
				shakeupdown : {
					eventCount: 0,
					intervalsPassed: 0,
					intervalsFreeze: 0
				}
			};
			var _oCurrentDevicePosition = {
				accelerationIncludingGravity : {
					x: event_.accelerationIncludingGravity.x,
					y: event_.accelerationIncludingGravity.y,
					z: event_.accelerationIncludingGravity.z
				},
				shake: {
					eventCount: _oLastDevicePosition.shake.eventCount,
					intervalsPassed: _oLastDevicePosition.shake.intervalsPassed,
					intervalsFreeze: _oLastDevicePosition.shake.intervalsFreeze
				 },
				 shakeleftright: {
					eventCount: _oLastDevicePosition.shakeleftright.eventCount,
					intervalsPassed: _oLastDevicePosition.shakeleftright.intervalsPassed,
					intervalsFreeze: _oLastDevicePosition.shakeleftright.intervalsFreeze
				 },
				 shakefrontback: {
					eventCount: _oLastDevicePosition.shakefrontback.eventCount,
					intervalsPassed: _oLastDevicePosition.shakefrontback.intervalsPassed,
					intervalsFreeze: _oLastDevicePosition.shakefrontback.intervalsFreeze
				 },
				 shakeupdown: {
					eventCount: _oLastDevicePosition.shakeupdown.eventCount,
					intervalsPassed: _oLastDevicePosition.shakeupdown.intervalsPassed,
					intervalsFreeze: _oLastDevicePosition.shakeupdown.intervalsFreeze
				 }

			};

			var _aType;
			var _aDescription;
			var _oObj;
			for (_sType in _oDatajQueryGestures) {
				switch(_sType) {

					case 'shake':
					case 'shakeleftright':
					case 'shakefrontback':
					case 'shakeupdown':
						_aType = [];
						_aDescription = [];
						_aType.push(_sType);
						if (++_oCurrentDevicePosition[_sType].intervalsFreeze > _oThreshold.freezeShakes && _oCurrentDevicePosition[_sType].intervalsFreeze < (2*_oThreshold.freezeShakes) ) { break;	}
						_oCurrentDevicePosition[_sType].intervalsFreeze  = 0;
						_oCurrentDevicePosition[_sType].intervalsPassed++;
						if ( ( _sType === 'shake' ||_sType === 'shakeleftright' ) && ( _oCurrentDevicePosition.accelerationIncludingGravity.x > _oThreshold.leftright.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.x < (-1* _oThreshold.leftright.sensitivity) ) ) {
							_aType.push('leftright');
							_aType.push('x-axis');
						}

						if ( ( _sType === 'shake' ||_sType === 'shakefrontback' ) && (_oCurrentDevicePosition.accelerationIncludingGravity.y > _oThreshold.frontback.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.y < (-1 * _oThreshold.frontback.sensitivity) ) ) {
							_aType.push('frontback');
							_aType.push('y-axis');
						}

						if ( ( _sType === 'shake' ||_sType === 'shakeupdown' ) && ( _oCurrentDevicePosition.accelerationIncludingGravity.z+9.81 > _oThreshold.updown.sensitivity  || _oCurrentDevicePosition.accelerationIncludingGravity.z+9.81 < (-1 * _oThreshold.updown.sensitivity) ) ) {
							_aType.push('updown');
							_aType.push('z-axis');
						}
						if (_aType.length > 1) {
							if (++_oCurrentDevicePosition[_sType].eventCount == _oThreshold.requiredShakes && (_oCurrentDevicePosition[_sType].intervalsPassed) < _oThreshold.freezeShakes ) {
								_$element.triggerHandler(_sType, _createOptions ({type: _sType, description: _aType.join(':'), event:event_,duration:_oCurrentDevicePosition[_sType].intervalsPassed*5 }) );
								_oCurrentDevicePosition[_sType].eventCount = 0;
								_oCurrentDevicePosition[_sType].intervalsPassed = 0;
								_oCurrentDevicePosition[_sType].intervalsFreeze = _oThreshold.freezeShakes+1;
							}
							else if (_oCurrentDevicePosition[_sType].eventCount == _oThreshold.requiredShakes && (_oCurrentDevicePosition[_sType].intervalsPassed) > _oThreshold.freezeShakes ) {
								_oCurrentDevicePosition[_sType].eventCount = 0 ;
								_oCurrentDevicePosition[_sType].intervalsPassed = 0;
							}
						}
					break;

				}
				_oObj = {};
				_oObj.oDeviceMotionLastDevicePosition = _oCurrentDevicePosition;
				_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));

			}
		}

		function _onTouchstart(event_) {
			var _$element = $(event_.currentTarget);
			_$element.triggerHandler($.jGestures.events.touchstart,event_);
			if($.hasGestures) {
				event_.currentTarget.addEventListener('touchmove', _onTouchmove, false);
				event_.currentTarget.addEventListener('touchend', _onTouchend, false);
			}
			else {
				_$element.bind('mousemove', _onTouchmove);
				_$element.bind('mouseup', _onTouchend);
			}
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _eventBase = (event_.touches) ? event_.touches[0] : event_;
			var _oObj = {};
			_oObj.oLastSwipemove = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};
			_oObj.oStartTouch = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};

			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
		}

		function _onTouchmove(event_) {

			var _$element = $(event_.currentTarget);
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');

			var _bHasTouches = !!event_.touches;
			var _iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
			var _iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;

			var _oEventData = _oDatajQueryGestures.oLastSwipemove;
			var _iDeltaX = _iScreenX - _oEventData.screenX   ;
			var _iDeltaY = _iScreenY - _oEventData.screenY;

			var _oDetails;
			if (!!_oDatajQueryGestures.oLastSwipemove) {
				_oDetails = _createOptions({type: 'swipemove', touches: (_bHasTouches) ? event_.touches.length: '1', screenY: _iScreenY,screenX:_iScreenX ,deltaY: _iDeltaY,deltaX : _iDeltaX, startMove:_oEventData, event:event_, timestamp:_oEventData.timestamp});
				_$element.triggerHandler(_oDetails.type,_oDetails);
			}
			var _oObj = {};
			var _eventBase = (event_.touches) ? event_.touches[0] : event_;
			_oObj.oLastSwipemove = { screenX : _eventBase.screenX, screenY : _eventBase.screenY, timestamp:new Date().getTime()};
			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
		}

		function _onTouchend(event_) {
			var _$element = $(event_.currentTarget);
			var _bHasTouches = !!event_.changedTouches;
			var _iTouches = (_bHasTouches) ? event_.changedTouches.length : '1';
			var _iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
			var _iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;
			_$element.triggerHandler($.jGestures.events.touchendStart,event_);

			if($.hasGestures) {
				event_.currentTarget.removeEventListener('touchmove', _onTouchmove, false);
				event_.currentTarget.removeEventListener('touchend', _onTouchend, false);
			}
			else {
				_$element.unbind('mousemove', _onTouchmove);
				_$element.unbind('mouseup', _onTouchend);
			}
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _bHasMoved = (
				Math.abs(_oDatajQueryGestures.oStartTouch.screenX - _iScreenX) > $.jGestures.defaults.thresholdMove ||
				Math.abs(_oDatajQueryGestures.oStartTouch.screenY - _iScreenY) > $.jGestures.defaults.thresholdMove
			) ? true : false;
			var _bHasSwipeGesture = (
				Math.abs(_oDatajQueryGestures.oStartTouch.screenX - _iScreenX) > $.jGestures.defaults.thresholdSwipe ||
				Math.abs(_oDatajQueryGestures.oStartTouch.screenY - _iScreenY) > $.jGestures.defaults.thresholdSwipe
			) ? true : false;

			var _sType;
			var _oEventData ;
			var _oDelta;
			var _iDeltaX;
			var _iDeltaY;
			var _oDetails;
			var _aDict = ['zero','one','two','three','four'];
			var _bIsSwipe;
			for (_sType in _oDatajQueryGestures) {
				_oEventData = _oDatajQueryGestures.oStartTouch;
				_oDelta = {};
				_iScreenX = (_bHasTouches) ? event_.changedTouches[0].screenX : event_.screenX;
				_iScreenY = (_bHasTouches) ? event_.changedTouches[0].screenY : event_.screenY;
				_iDeltaX = _iScreenX - _oEventData.screenX ;
				_iDeltaY = _iScreenY - _oEventData.screenY;
				_oDetails = _createOptions({type: 'swipe', touches: _iTouches, screenY: _iScreenY,screenX:_iScreenX ,deltaY: _iDeltaY,deltaX : _iDeltaX, startMove:_oEventData, event:event_, timestamp:  _oEventData.timestamp });
				_bIsSwipe = false;
				switch(_sType) {
					case 'swipeone':
						if( _bHasTouches === false && _iTouches == 1 && _bHasMoved === false){
							break;
						}
						if (_bHasTouches===false || ( _iTouches == 1  && _bHasMoved === true && _bHasSwipeGesture===true)) {
							_bIsSwipe = true;

							_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
							_$element.triggerHandler(_oDetails.type,_oDetails);
						}
					break;
					case 'swipetwo':
						if (( _bHasTouches && _iTouches== 2 && _bHasMoved === true && _bHasSwipeGesture===true)) {
							_bIsSwipe = true;
							_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
							_$element.triggerHandler(_oDetails.type,_oDetails);
						}
					break;
					case 'swipethree':
						if ( ( _bHasTouches && _iTouches == 3 && _bHasMoved === true && _bHasSwipeGesture===true)) {
							_bIsSwipe = true;
							_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
							_$element.triggerHandler(_oDetails.type,_oDetails);
						}
					break;
					case 'swipefour':
						if ( ( _bHasTouches && _iTouches == 4 && _bHasMoved === true && _bHasSwipeGesture===true)) {
							_bIsSwipe = true;
							_oDetails.type = ['swipe',_aDict[_iTouches]].join('');
							_$element.triggerHandler(_oDetails.type,_oDetails);
						}
					break;
					case 'swipeup':
					case 'swiperightup':
					case 'swiperight':
					case 'swiperightdown':
					case 'swipedown':
					case 'swipeleftdown':
					case 'swipeleft':
					case 'swipeleftup':
						if ( _bHasTouches && _bHasMoved === true && _bHasSwipeGesture===true) {
							_bIsSwipe = true;
							_oDetails.type = [
										'swipe',
									((_oDetails.delta[0].lastX != 0) ? ((_oDetails.delta[0].lastX > 0) ? 'right' : 'left') : ''),
									((_oDetails.delta[0].lastY != 0) ? ((_oDetails.delta[0].lastY > 0) ? 'down' : 'up') :'')
										].join('');
							_$element.triggerHandler(_oDetails.type, _oDetails);
						}
					break;

					case 'tapone':
					case 'taptwo':
					case 'tapthree':
					case 'tapfour':
						if (( /* _bHasTouches && */ _bHasMoved !== true && _bIsSwipe !==true) && (_aDict[_iTouches] ==_sType.slice(3)) ) {
							_oDetails.description = ['tap',_aDict[_iTouches]].join('');
							_oDetails.type = ['tap',_aDict[_iTouches]].join('');
							_$element.triggerHandler(_oDetails.type,_oDetails);
							}
						break;

				}

				var _oObj = {};
				_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
				_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));

			}
			_$element.triggerHandler($.jGestures.events.touchendProcessed,event_);
		}

		function _onGesturestart(event_) {
			var _$element = $(event_.currentTarget);
			_$element.triggerHandler($.jGestures.events.gesturestart,event_);
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _oObj = {};
			_oObj.oStartTouch = {timestamp:new Date().getTime()};
			_$element.data('ojQueryGestures',$.extend(true,_oDatajQueryGestures,_oObj));
		}

		function _onGesturechange(event_) {
			var _$element = $(event_.currentTarget);
			var _iDelta,_iDirection,_sDesc,_oDetails;
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _sType;
			for (_sType in _oDatajQueryGestures) {
				switch(_sType) {
					case 'pinch':
						_iDelta = event_.scale;
						if ( ( ( _iDelta < 1 ) && (_iDelta % 1) < (1 - $.jGestures.defaults.thresholdPinchclose) ) || ( ( _iDelta > 1 ) && (_iDelta % 1) > ($.jGestures.defaults.thresholdPinchopen) ) ) {
							_iDirection = (_iDelta < 1 ) ? -1 : +1 ;
							_oDetails = _createOptions({type: 'pinch', scale: _iDelta, touches: null,startMove:_oDatajQueryGestures.oStartTouch, event:event_, timestamp: _oDatajQueryGestures.oStartTouch.timestamp, vector:_iDirection, description: ['pinch:',_iDirection,':' , ( (_iDelta < 1 ) ? 'close' : 'open' )].join('') });
							_$element.triggerHandler(_oDetails.type, _oDetails);
						}
					break;

					case 'rotate':
						_iDelta = event_.rotation;
						if ( ( ( _iDelta < 1 ) &&  ( -1*(_iDelta) > $.jGestures.defaults.thresholdRotateccw ) ) || ( ( _iDelta > 1 ) && (_iDelta  > $.jGestures.defaults.thresholdRotatecw) ) ) {
							_iDirection = (_iDelta < 1 ) ? -1 : +1 ;
							_oDetails = _createOptions({type: 'rotate', rotation: _iDelta, touches: null, startMove:_oDatajQueryGestures.oStartTouch, event:event_, timestamp: _oDatajQueryGestures.oStartTouch.timestamp, vector:_iDirection, description: ['rotate:',_iDirection,':' , ( (_iDelta < 1 ) ? 'counterclockwise' : 'clockwise' )].join('') });
							_$element.triggerHandler(_oDetails.type, _oDetails);
						}
					break;

				}
			}

		}

		function _onGestureend(event_) {
			var _$element = $(event_.currentTarget);
			_$element.triggerHandler($.jGestures.events.gestureendStart,event_);
			var _iDelta;
			var _oDatajQueryGestures = _$element.data('ojQueryGestures');
			var _sType;
			for (_sType in _oDatajQueryGestures) {
				switch(_sType) {
					case 'pinchclose':
						_iDelta = event_.scale;
						if (( _iDelta < 1 ) && (_iDelta % 1) < (1 - $.jGestures.defaults.thresholdPinchclose)) {
							_$element.triggerHandler('pinchclose', _createOptions ({type: 'pinchclose', scale:_iDelta, vector: -1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'pinch:-1:close' }) );
						}
					break;
					case 'pinchopen':
						_iDelta = event_.scale;
						if ( ( _iDelta > 1 ) && (_iDelta % 1) > ($.jGestures.defaults.thresholdPinchopen) ) {
							_$element.triggerHandler('pinchopen', _createOptions ({type: 'pinchopen', scale:_iDelta, vector: +1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'pinch:+1:open'}) );
						}
					break;
					case 'rotatecw':
						_iDelta = event_.rotation;
						if ( ( _iDelta > 1 ) && (_iDelta  > $.jGestures.defaults.thresholdRotatecw) ) {
							_$element.triggerHandler('rotatecw', _createOptions ({type: 'rotatecw', rotation:_iDelta, vector: +1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'rotate:+1:clockwise'}) );
						}
					break;
					case 'rotateccw':
						_iDelta = event_.rotation;
						if ( ( _iDelta < 1 ) &&  ( -1*(_iDelta) > $.jGestures.defaults.thresholdRotateccw ) ) {
								_$element.triggerHandler('rotateccw', _createOptions ({type: 'rotateccw', rotation:_iDelta, vector: -1, touches: null, startMove: _oDatajQueryGestures.oStartTouch, event:event_, timestamp:_oDatajQueryGestures.oStartTouch.timestamp,description: 'rotate:-1:counterclockwise'}) );
							}
					break;
				}
			}
			_$element.triggerHandler($.jGestures.events.gestureendProcessed,event_);
		}
	})(window.jQuery || require('jquery'));
});


	

