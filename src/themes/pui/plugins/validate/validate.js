/**
 @Name：validate V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-21
 */
 (function( factory ) {
	define('pui/validate',['jquery'],function(require,exports,moudles){
		moudles.exports = factory(require("jquery"));
	});
}(function( $ ) {
$.extend($.fn, {
	// 验证所选的FORM
	validate: function( options ) {
		var validator = $.data(this[0],"validator");
		if (!this.length || validator) {
			return (validator || this);
		}
		this.attr( "novalidate", "novalidate" );

		validator = new $.validator( options, this[ 0 ] );
		$.data( this[ 0 ], "validator", validator );
		if ( validator.settings.onsubmit ) {

			this.validateDelegate( ":submit", "click", function( event ) {
				if ( validator.settings.submitHandler ) {
					validator.submitButton = event.target;
				}

				if ( $( event.target ).hasClass( "cancel" ) ) {
					validator.cancelSubmit = true;
				}

				if ( $( event.target ).attr( "formnovalidate" ) !== undefined ) {
					validator.cancelSubmit = true;
				}
			});

			this.submit( function( event ) {
				if( validator.settings.debug ){
					event.preventDefault();
				}
				function handle() {
					var hidden, result;
					if ( validator.settings.submitHandler ) {
						if ( validator.submitButton ) {
							// insert a hidden input as a replacement for the missing submit button
							hidden = $( "<input type='hidden'/>" )
								.attr( "name", validator.submitButton.name )
								.val( $( validator.submitButton ).val() )
								.appendTo( validator.currentForm );
						}
						result = validator.settings.submitHandler.call( validator, validator.currentForm, event );
						if ( validator.submitButton ) {
							// and clean up afterwards; thanks to no-block-scope, hidden can be referenced
							hidden.remove();
						}
						if ( result !== undefined ) {
							return result;
						}
						return false;
					}
					return true;
				}

				// prevent submit for invalid forms or custom submit handlers
				if ( validator.cancelSubmit ) {
					validator.cancelSubmit = false;
					return handle();
				}
				if ( validator.form() ) {
					if ( validator.pendingRequest ) {
						validator.formSubmitted = true;
						return false;
					}
					return handle();
				} else {
					validator.focusInvalid();
					return false;
				}
			}).bind('reset',function(){
				validator && validator.resetForm();
			});
		}

		return validator;
	},
	// 检查是否验证通过
	valid: function() {
		var valid, validator;
		if ( $( this[ 0 ] ).is( "form" ) ) {
			valid = this.validate().form();
		} else {
			valid = true;
			validator = $( this[ 0 ].form ).validate();
			this.each( function() {
				valid = validator.element( this ) && valid;
			});
		}
		return valid;
	},
	// 返回（增加/删除）验证规则
	rules: function( command, argument ) {
		var element = this[ 0 ],
			settings, staticRules, existingRules, data, param, filtered;

		if ( command ) {
			settings = $.data( element.form, "validator" ).settings;
			staticRules = settings.rules;
			existingRules = $.validator.staticRules( element );
			switch ( command ) {
			case "add":
				$.extend( existingRules, $.validator.normalizeRule( argument ) );
				// remove messages from rules, but allow them to be set separately
				delete existingRules.messages;
				staticRules[ element.name ] = existingRules;
				if ( argument.messages ) {
					settings.messages[ element.name ] = $.extend( settings.messages[ element.name ], argument.messages );
				}
				break;
			case "remove":
				if ( !argument ) {
					delete staticRules[ element.name ];
					return existingRules;
				}
				filtered = {};
				$.each( argument.split( /\s/ ), function( index, method ) {
					filtered[ method ] = existingRules[ method ];
					delete existingRules[ method ];
					if ( method === "required" ) {
						$( element ).removeAttr( "aria-required" );
					}
				});
				return filtered;
			}
		}

		data = $.validator.normalizeRules(
		$.extend(
			{},
			$.validator.classRules( element ),
			$.validator.attributeRules( element ),
			$.validator.dataRules( element ),
			$.validator.staticRules( element )
		), element );

		// make sure required is at front
		if ( data.required ) {
			param = data.required;
			delete data.required;
			data = $.extend( { required: param }, data );
			$( element ).attr( "aria-required", "true" );
		}

		// make sure remote is at back
		if ( data.remote ) {
			param = data.remote;
			delete data.remote;
			data = $.extend( data, { remote: param });
		}

		return data;
	}
});

// 验证对象原型
$.validator = function( options, form ) {
	this.settings = $.extend( true, {}, $.validator.defaults, options );
	this.currentForm = form;
	this.init();
};
// 模板化字符串
$.validator.format = function( source, params ) {
	if ( arguments.length === 1 ) {
		return function() {
			var args = $.makeArray( arguments );
			args.unshift( source );
			return $.validator.format.apply( this, args );
		};
	}
	if ( arguments.length > 2 && params.constructor !== Array  ) {
		params = $.makeArray( arguments ).slice( 1 );
	}
	if ( params.constructor !== Array ) {
		params = [ params ];
	}
	$.each( params, function( i, n ) {
		source = source.replace( new RegExp( "\\{" + i + "\\}", "g" ), function() {
			return n;
		});
	});
	return source;
};
// 默认参数
$.extend( $.validator, {
	defaults: {
		messages: {},
		groups: {},
		rules: {},
		errorClass: "error",
		errorMessageCss: "errorMessage",
		validClass: "valid",
		errorElement: "label",
		focusCleanup: false,
		focusInvalid: true,
		errorContainer: $( [] ),
		errorLabelContainer: $( [] ),
		onsubmit: true,
		ignore: ":hidden:not(.jqTransformHidden)",
		ignoreTitle: false,
		onfocusin: function( element ) {
			this.lastActive = element;
			if ( this.settings.focusCleanup ) {
				if ( this.settings.unhighlight ) {
					this.settings.unhighlight.call( this, element, this.settings.errorClass, this.settings.validClass );
				}
				this.hideThese( this.errorsFor( element ) );
			}
		},
		onfocusout: function( element ) {
			if ( !this.checkable( element ) && ( element.name in this.submitted || !this.optional( element ) ) ) {
				this.element( element );
			}
		},
		onkeyup: function( element, event ) {
			if ( event.which === 9 && this.elementValue( element ) === "" ) {
				return;
			} else if ( element.name in this.submitted || element === this.lastElement ) {
				this.element( element );
			}
		},
		onclick: function( element ) {
			if ( element.name in this.submitted ) {
				this.element( element );
			} else if ( element.parentNode.name in this.submitted ) {
				this.element( element.parentNode );
			}
		},
		highlight: function( element, errorClass, validClass ) {
			if ( element.type === "radio" ) {
				this.findByName( element.name ).addClass( errorClass ).removeClass( validClass );
			} else {
				$( element ).addClass( errorClass ).removeClass( validClass );
			}
		},
		unhighlight: function( element, errorClass, validClass ) {
			if ( element.type === "radio" ) {
				this.findByName( element.name ).removeClass( errorClass ).addClass( validClass );
			} else {
				$( element ).removeClass( errorClass ).addClass( validClass );
			}
		},
		showErrors: function(errorMap,errorList){
			var _this = this;
			pui.use(['pui/tooltip'],function(U){
				for ( var i = 0; errorList[i]; i++ ) {
					$(errorList[i].element).tooltip({
						title: errorList[i].message,
						align : 'mr',
						show : true
					});
					this.toShow = $(this.toShow).add(errorList[i].element);
				}
				$.fn.tooltip && $(_this.successList).tooltip({ hide : true });
				this.toShow = $(this.toShow).not(_this.successList);
			});
		},
		hideErrors: function(){
			pui.use(['pui/tooltip'],function(U){
				$(this.toShow).tooltip({ hide : true });
				this.toShow = $(this.toShow).not(this.toShow);
			});
		}
	},
	setDefaults: function( settings ) {
		$.extend( $.validator.defaults, settings );
	},
	messages: {
		required: "该字段为必填项",
		remote: "该字段内容不正确",
		email: "请输入正确格式的电子邮件",
		url: "请输入合法的网址",
		date: "请输入合法的日期",
		dateISO: "请输入合法的日期格式",
		number: "请输入合法的数字",
		digits: "只能输入整数",
		creditcard: "请输入合法的信用卡号",
		equalTo: "请再次输入相同的值",
		maxlength: $.validator.format( "请输入一个长度最多是 {0} 的字符串" ),
		minlength: $.validator.format( "请输入一个长度最少是 {0} 的字符串" ),
		rangelength: $.validator.format( "请输入一个长度介于 {0} 和 {1} 之间的字符串" ),
		range: $.validator.format( "请输入一个介于 {0} 和 {1} 之间的值" ),
		max: $.validator.format( "请输入一个最大为 {0} 的值" ),
		min: $.validator.format( "请输入一个最小为 {0} 的值" )
	},
	autoCreateRanges: false,
	prototype: {
		init: function() {
			this.labelContainer = $( this.settings.errorLabelContainer );
			this.errorContext = this.labelContainer.length && this.labelContainer || $( this.currentForm );
			this.containers = $( this.settings.errorContainer ).add( this.settings.errorLabelContainer );
			this.submitted = {};
			this.valueCache = {};
			this.pendingRequest = 0;
			this.pending = {};
			this.invalid = {};
			this.reset();

			var groups = ( this.groups = {} ),
				rules;
			$.each( this.settings.groups, function( key, value ) {
				if ( typeof value === "string" ) {
					value = value.split( /\s/ );
				}
				$.each( value, function( index, name ) {
					groups[ name ] = key;
				});
			});
			rules = this.settings.rules;
			$.each( rules, function( key, value ) {
				rules[ key ] = $.validator.normalizeRule( value );
			});

			function delegate( event ) {
				var validator = $.data( this[ 0 ].form, "validator" ),
					eventType = "on" + event.type.replace( /^validate/, "" ),
					settings = validator ? validator.settings : {};
				if ( settings[ eventType ] && !this.is( settings.ignore ) ) {
					settings[ eventType ].call( validator, this[ 0 ], event );
				}
			}
			$( this.currentForm )
				.validateDelegate( ":text, [type='password'], [type='file'], select, textarea, " +
					"[type='number'], [type='search'] ,[type='tel'], [type='url'], " +
					"[type='email'], [type='datetime'], [type='date'], [type='month'], " +
					"[type='week'], [type='time'], [type='datetime-local'], " +
					"[type='range'], [type='color'], [type='radio'], [type='checkbox']",
					"focusin focusout keyup", delegate)
				.validateDelegate("select, option, [type='radio'], [type='checkbox']", "click", delegate);

			if ( this.settings.invalidHandler ) {
				$( this.currentForm ).bind( "invalid-form.validate", this.settings.invalidHandler );
			}
			$( this.currentForm ).find( "[required], [data-rule-required], .required" ).attr( "aria-required", "true" );
		},
		form: function() {
			this.checkForm();
			$.extend( this.submitted, this.errorMap );
			this.invalid = $.extend({}, this.errorMap );
			if ( !this.valid() ) {
				$( this.currentForm ).triggerHandler( "invalid-form", [ this ]);
			}
			this.showErrors();
			return this.valid();
		},
		checkForm: function() {
			this.prepareForm();
			for ( var i = 0, elements = ( this.currentElements = this.elements() ); elements[ i ]; i++ ) {
				this.check( elements[ i ] );
			}
			return this.valid();
		},
		element: function( element ) {
			var cleanElement = this.clean( element ),
				checkElement = this.validationTargetFor( cleanElement ),
				result = true;

			this.lastElement = checkElement;

			if ( checkElement === undefined ) {
				delete this.invalid[ cleanElement.name ];
			} else {
				this.prepareElement( checkElement );
				this.currentElements = $( checkElement );

				result = this.check( checkElement ) !== false;
				if ( result ) {
					delete this.invalid[ checkElement.name ];
				} else {
					this.invalid[ checkElement.name ] = true;
				}
			}
			// Add aria-invalid status for screen readers
			$( element ).attr( "aria-invalid", !result );

			if ( !this.numberOfInvalids() ) {
				// Hide error containers on last error
				this.toHide = this.toHide.add( this.containers );
			}
			this.showErrors();
			return result;
		},
		showErrors: function( errors ) {
			if ( errors ) {
				// add items to error list and map
				$.extend( this.errorMap, errors );
				this.errorList = [];
				for ( var name in errors ) {
					this.errorList.push({
						message: errors[ name ],
						element: this.findByName( name )[ 0 ]
					});
				}
				// remove items from success list
				this.successList = $.grep( this.successList, function( element ) {
					return !( element.name in errors );
				});
			}
			if ( this.settings.showErrors ) {
				this.settings.showErrors.call( this, this.errorMap, this.errorList );
			} else {
				this.defaultShowErrors();
			}
		},
		resetForm: function() {
			if ( $.fn.resetForm ) {
				$( this.currentForm ).resetForm();
			}
			this.submitted = {};
			this.lastElement = null;
			this.prepareForm();
			this.hideErrors();
			this.elements()
					.removeClass( this.settings.errorClass )
					.removeData( "previousValue" )
					.removeAttr( "aria-invalid" );
		},
		numberOfInvalids: function() {
			return this.objectLength( this.invalid );
		},
		objectLength: function( obj ) {
			var count = 0,
				i;
			for ( i in obj ) {
				count++;
			}
			return count;
		},
		hideErrors: function() {
			if ( this.settings.hideErrors ) {
				this.settings.hideErrors.call( this);
			} else {
				this.hideThese( this.toHide );
			}
		},
		hideThese: function( errors ) {
			errors.not( this.containers ).text( "" );
			this.addWrapper( errors ).hide();
		},

		valid: function() {
			return this.size() === 0;
		},

		size: function() {
			return this.errorList.length;
		},

		focusInvalid: function() {
			if ( this.settings.focusInvalid ) {
				try {
					$( this.findLastActive() || this.errorList.length && this.errorList[ 0 ].element || [])
					.filter( ":visible" )
					.focus()
					.trigger( "focusin" );
				} catch ( e ) {
				}
			}
		},

		findLastActive: function() {
			var lastActive = this.lastActive;
			return lastActive && $.grep( this.errorList, function( n ) {
				return n.element.name === lastActive.name;
			}).length === 1 && lastActive;
		},

		elements: function() {
			var validator = this,
				rulesCache = {};

			// select all valid inputs inside the form (no submit or reset buttons)
			return $( this.currentForm )
			.find( "input, select, textarea" )
			.not( ":submit, :reset, :image, [disabled]" )
			.not( this.settings.ignore )
			.filter( function() {
				if ( !validator.objectLength( $( this ).rules() ) ) {
					return false;
				}

				rulesCache[ this.name ] = true;
				return true;
			});
		},

		clean: function( selector ) {
			return $( selector )[ 0 ];
		},

		errors: function() {
			var errorMessageCss = this.settings.errorMessageCss.split( " " ).join( "." );
			return $( this.settings.errorElement + "." + errorMessageCss, this.errorContext );
		},

		reset: function() {
			this.successList = [];
			this.errorList = [];
			this.errorMap = {};
			this.toShow = $( [] );
			this.toHide = $( [] );
			this.currentElements = $( [] );
		},

		prepareForm: function() {
			this.reset();
			this.toHide = this.errors().add( this.containers );
		},

		prepareElement: function( element ) {
			this.reset();
			this.toHide = this.errorsFor( element );
		},

		elementValue: function( element ) {
			var val,
				$element = $( element ),
				type = element.type;

			if ( type === "radio" || type === "checkbox" ) {
				return $( "input[name='" + element.name + "']:checked" ).val();
			} else if ( type === "number" && typeof element.validity !== "undefined" ) {
				return element.validity.badInput ? false : $element.val();
			}

			val = $element.val();
			if ( typeof val === "string" ) {
				return val.replace(/\r/g, "" );
			}
			return val;
		},

		check: function( element ) {
			element = this.validationTargetFor( this.clean( element ) );

			var rules = $( element ).rules(),
				rulesCount = $.map( rules, function( n, i ) {
					return i;
				}).length,
				dependencyMismatch = false,
				val = this.elementValue( element ),
				result, method, rule;

			for ( method in rules ) {
				rule = { method: method, parameters: rules[ method ] };
				try {

					result = $.validator.methods[ method ].call( this, val, element, rule.parameters );
					if ( result === "dependency-mismatch" && rulesCount === 1 ) {
						dependencyMismatch = true;
						continue;
					}
					dependencyMismatch = false;

					if ( result === "pending" ) {
						this.toHide = this.toHide.not( this.errorsFor( element ) );
						return;
					}

					if ( !result ) {
						this.formatAndAdd( element, rule );
						return false;
					}
				} catch ( e ) {
					throw e;
				}
			}
			if ( dependencyMismatch ) {
				return;
			}
			if ( this.objectLength( rules ) ) {
				this.successList.push( element );
			}
			return true;
		},
		customDataMessage: function( element, method ) {
			return $( element ).data( "msg" + method.charAt( 0 ).toUpperCase() +
				method.substring( 1 ).toLowerCase() ) || $( element ).data( "msg" );
		},

		// return the custom message for the given element name and validation method
		customMessage: function( name, method ) {
			var m = this.settings.messages[ name ];
			return m && ( m.constructor === String ? m : m[ method ]);
		},

		// return the first defined argument, allowing empty strings
		findDefined: function() {
			for ( var i = 0; i < arguments.length; i++) {
				if ( arguments[ i ] !== undefined ) {
					return arguments[ i ];
				}
			}
			return undefined;
		},

		defaultMessage: function( element, method ) {
			return this.findDefined(
				this.customMessage( element.name, method ),
				this.customDataMessage( element, method ),
				// title is never undefined, so handle empty string as undefined
				!this.settings.ignoreTitle && element.title || undefined,
				$.validator.messages[ method ],
				"<strong>Warning: No message defined for " + element.name + "</strong>"
			);
		},

		formatAndAdd: function( element, rule ) {
			var message = this.defaultMessage( element, rule.method ),
				theregex = /\$?\{(\d+)\}/g;
			if ( typeof message === "function" ) {
				message = message.call( this, rule.parameters, element );
			} else if ( theregex.test( message ) ) {
				message = $.validator.format( message.replace( theregex, "{$1}" ), rule.parameters );
			}
			this.errorList.push({
				message: message,
				element: element,
				method: rule.method
			});

			this.errorMap[ element.name ] = message;
			this.submitted[ element.name ] = message;
		},

		addWrapper: function( toToggle ) {
			if ( this.settings.wrapper ) {
				toToggle = toToggle.add( toToggle.parent( this.settings.wrapper ) );
			}
			return toToggle;
		},

		defaultShowErrors: function() {
			var i, elements, error;
			for ( i = 0; this.errorList[ i ]; i++ ) {
				error = this.errorList[ i ];
				if ( this.settings.highlight ) {
					this.settings.highlight.call( this, error.element, this.settings.errorClass, this.settings.validClass );
				}
				this.showLabel( error.element, error.message );
			}
			if ( this.errorList.length ) {
				this.toShow = this.toShow.add( this.containers );
			}
			if ( this.settings.success ) {
				for ( i = 0; this.successList[ i ]; i++ ) {
					this.showLabel( this.successList[ i ] );
				}
			}
			if ( this.settings.unhighlight ) {
				for ( i = 0, elements = this.validElements(); elements[ i ]; i++ ) {
					this.settings.unhighlight.call( this, elements[ i ], this.settings.errorClass, this.settings.validClass );
				}
			}
			this.toHide = this.toHide.not( this.toShow );
			this.hideErrors();
			this.addWrapper( this.toShow ).show();
		},

		validElements: function() {
			return this.currentElements.not( this.invalidElements() );
		},

		invalidElements: function() {
			return $( this.errorList ).map(function() {
				return this.element;
			});
		},

		showLabel: function( element, message ) {
			var place, group, errorID,
				error = this.errorsFor( element ),
				elementID = this.idOrName( element ),
				describedBy = $( element ).attr( "aria-describedby" );
			if ( error.length ) {
				error.removeClass( this.settings.validClass ).addClass( this.settings.errorClass );
				error.html( message );
			} else {
				// create error element
				error = $( "<" + this.settings.errorElement + ">" )
					.attr( "id", elementID + "-error" )
					.addClass( this.settings.errorMessageCss )
					.html( message || "" );
				place = error;
				if ( this.settings.wrapper ) {
					place = error.hide().show().wrap( "<" + this.settings.wrapper + "/>" ).parent();
				}
				if ( this.labelContainer.length ) {
					this.labelContainer.append( place );
				} else if ( this.settings.errorPlacement ) {
					this.settings.errorPlacement( place, $( element ) );
				} else {
					place.insertAfter( element );
				}

				if ( error.is( "label" ) ) {
					error.attr( "for", elementID );
				} else if ( error.parents( "label[for='" + elementID + "']" ).length === 0 ) {
					errorID = error.attr( "id" ).replace( /(:|\.|\[|\])/g, "\\$1");
					if ( !describedBy ) {
						describedBy = errorID;
					} else if ( !describedBy.match( new RegExp( "\\b" + errorID + "\\b" ) ) ) {
						describedBy += " " + errorID;
					}
					$( element ).attr( "aria-describedby", describedBy );
					group = this.groups[ element.name ];
					if ( group ) {
						$.each( this.groups, function( name, testgroup ) {
							if ( testgroup === group ) {
								$( "[name='" + name + "']", this.currentForm )
									.attr( "aria-describedby", error.attr( "id" ) );
							}
						});
					}
				}
			}
			if ( !message && this.settings.success ) {
				error.text( "" );
				if ( typeof this.settings.success === "string" ) {
					error.addClass( this.settings.success );
				} else {
					this.settings.success( error, element );
				}
			}
			this.toShow = this.toShow.add( error );
		},

		errorsFor: function( element ) {
			var name = this.idOrName( element ),
				describer = $( element ).attr( "aria-describedby" ),
				selector = "label[for='" + name + "'], label[for='" + name + "'] *";
			if ( describer ) {
				selector = selector + ", #" + describer.replace( /\s+/g, ", #" );
			}
			return this
				.errors()
				.filter( selector );
		},

		idOrName: function( element ) {
			return this.groups[ element.name ] || $(element).data('validateName') || ( this.checkable( element ) ? element.name : element.id || element.name ) || this.createName(element);
		},
		validationTargetFor: function( element ) {
			if ( this.checkable( element ) ) {
				element = this.findByName( element.name );
			}
			return $( element ).not( this.settings.ignore )[ 0 ];
		},
		createName: function(element){
			var t=$(element),validateName=t.data('validateName');
			if(!validateName){validateName='__el'+Math.floor(Math.random()*999+1);}
			t.data('validateName',validateName);
			return validateName;
		},
		checkable: function( element ) {
			return ( /radio|checkbox/i ).test( element.type );
		},

		findByName: function( name ) {
			return $( this.currentForm ).find( "[name='" + name + "']" );
		},

		getLength: function( value, element ) {
			switch ( element.nodeName.toLowerCase() ) {
			case "select":
				return $( "option:selected", element ).length;
			case "input":
				if ( this.checkable( element ) ) {
					return this.findByName( element.name ).filter( ":checked" ).length;
				}
			}
			return value.length;
		},

		depend: function( param, element ) {
			return this.dependTypes[typeof param] ? this.dependTypes[typeof param]( param, element ) : true;
		},

		dependTypes: {
			"boolean": function( param ) {
				return param;
			},
			"string": function( param, element ) {
				return !!$( param, element.form ).length;
			},
			"function": function( param, element ) {
				return param( element );
			}
		},

		optional: function( element ) {
			var val = this.elementValue( element );
			return !$.validator.methods.required.call( this, val, element ) && "dependency-mismatch";
		},

		startRequest: function( element ) {
			if ( !this.pending[ element.name ] ) {
				this.pendingRequest++;
				this.pending[ element.name ] = true;
			}
		},

		stopRequest: function( element, valid ) {
			this.pendingRequest--;
			// sometimes synchronization fails, make sure pendingRequest is never < 0
			if ( this.pendingRequest < 0 ) {
				this.pendingRequest = 0;
			}
			delete this.pending[ element.name ];
			if ( valid && this.pendingRequest === 0 && this.formSubmitted && this.form() ) {
				$( this.currentForm ).submit();
				this.formSubmitted = false;
			} else if (!valid && this.pendingRequest === 0 && this.formSubmitted ) {
				$( this.currentForm ).triggerHandler( "invalid-form", [ this ]);
				this.formSubmitted = false;
			}
		},

		previousValue: function( element ) {
			return $.data( element, "previousValue" ) || $.data( element, "previousValue", {
				old: null,
				valid: true,
				message: this.defaultMessage( element, "remote" )
			});
		}

	},

	classRuleSettings: {
		required: { required: true },
		email: { email: true },
		url: { url: true },
		date: { date: true },
		dateISO: { dateISO: true },
		number: { number: true },
		digits: { digits: true },
		creditcard: { creditcard: true }
	},

	addClassRules: function( className, rules ) {
		if ( className.constructor === String ) {
			this.classRuleSettings[ className ] = rules;
		} else {
			$.extend( this.classRuleSettings, className );
		}
	},

	classRules: function( element ) {
		var rules = {},
			classes = $( element ).attr( "class" );

		if ( classes ) {
			$.each( classes.split( " " ), function() {
				if ( this in $.validator.classRuleSettings ) {
					$.extend( rules, $.validator.classRuleSettings[ this ]);
				}
			});
		}
		return rules;
	},

	attributeRules: function( element ) {
		var rules = {},
			$element = $( element ),
			type = element.getAttribute( "type" ),
			method, value;

		for ( method in $.validator.methods ) {
			if ( method === "required" ) {
				value = element.getAttribute( method );
				if ( value === "" ) {
					value = true;
				}
				value = !!value;
			} else {
				value = $element.attr( method );
			}
			if ( /min|max/.test( method ) && ( type === null || /number|range|text/.test( type ) ) ) {
				value = Number( value );
			}

			if ( value || value === 0 ) {
				rules[ method ] = value;
			} else if ( type === method && type !== "range" ) {
				rules[ method ] = true;
			}
		}

		// maxlength may be returned as -1, 2147483647 ( IE ) and 524288 ( safari ) for text inputs
		if ( rules.maxlength && /-1|2147483647|524288/.test( rules.maxlength ) ) {
			delete rules.maxlength;
		}

		return rules;
	},

	dataRules: function( element ) {
		var method, value,
			rules = {}, $element = $( element );
		for ( method in $.validator.methods ) {
			value = $element.data( "rule" + method.charAt( 0 ).toUpperCase() + method.substring( 1 ).toLowerCase() );
			if ( value !== undefined ) {
				rules[ method ] = value;
			}
		}
		return rules;
	},

	staticRules: function( element ) {
		var rules = {},
			validator = $.data( element.form, "validator" );

		if (validator && validator.settings && validator.settings.rules ) {
			rules = $.validator.normalizeRule( validator.settings.rules[ element.name ] ) || {};
		}
		return rules;
	},
	normalizeRules: function( rules, element ) {
		$.each( rules, function( prop, val ) {
			// ignore rule when param is explicitly false, eg. required:false
			if ( val === false ) {
				delete rules[ prop ];
				return;
			}
			if ( val.param || val.depends ) {
				var keepRule = true;
				switch ( typeof val.depends ) {
				case "string":
					keepRule = !!$( val.depends, element.form ).length;
					break;
				case "function":
					keepRule = val.depends.call( element, element );
					break;
				}
				if ( keepRule ) {
					rules[ prop ] = val.param !== undefined ? val.param : true;
				} else {
					delete rules[ prop ];
				}
			}
		});

		// evaluate parameters
		$.each( rules, function( rule, parameter ) {
			rules[ rule ] = $.isFunction( parameter ) ? parameter( element ) : parameter;
		});

		// clean number parameters
		$.each([ "minlength", "maxlength" ], function() {
			if ( rules[ this ] ) {
				rules[ this ] = Number( rules[ this ] );
			}
		});
		$.each([ "rangelength", "range" ], function() {
			var parts;
			if ( rules[ this ] ) {
				if ( $.isArray( rules[ this ] ) ) {
					rules[ this ] = [ Number( rules[ this ][ 0 ]), Number( rules[ this ][ 1 ] ) ];
				} else if ( typeof rules[ this ] === "string" ) {
					parts = rules[ this ].replace(/[\[\]]/g, "" ).split( /[\s,]+/ );
					rules[ this ] = [ Number( parts[ 0 ]), Number( parts[ 1 ] ) ];
				}
			}
		});

		if ( $.validator.autoCreateRanges ) {
			// auto-create ranges
			if ( rules.min != null && rules.max != null ) {
				rules.range = [ rules.min, rules.max ];
				delete rules.min;
				delete rules.max;
			}
			if ( rules.minlength != null && rules.maxlength != null ) {
				rules.rangelength = [ rules.minlength, rules.maxlength ];
				delete rules.minlength;
				delete rules.maxlength;
			}
		}

		return rules;
	},
	// 字符串转化为规则
	normalizeRule: function( data ) {
		if ( typeof data === "string" ) {
			var transformed = {};
			$.each( data.split( /\s/ ), function() {
				transformed[ this ] = true;
			});
			data = transformed;
		}
		return data;
	},
	// 验证方法
	methods: {
		// 是否必填
		required: function( value, element, param ) {
			if ( !this.depend( param, element ) ) {
				return "dependency-mismatch";
			}
			if ( element.nodeName.toLowerCase() === "select" ) {
				var val = $( element ).val();
				return val && val.length > 0;
			}
			if ( this.checkable( element ) ) {
				return this.getLength( value, element ) > 0;
			}
			return $.trim( value ).length > 0;
		},
		// 是否邮箱地址
		email: function( value, element ) {
			return this.optional( element ) || /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test( value );
		},
		// 是否网址
		url: function( value, element ) {
			return this.optional( element ) || /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		},
		// 是否日期
		date: function( value, element ) {
			return this.optional( element ) || !/Invalid|NaN/.test( new Date( value ).toString() );
		},
		// 是否格式化的日期
		dateISO: function( value, element ) {
			return this.optional( element ) || /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test( value );
		},
		// 数字
		number: function( value, element ) {
			return this.optional( element ) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test( value );
		},
		// 只能整数
		digits: function( value, element ) {
			return this.optional( element ) || /^-?\d+$/.test( value );
		},
		// 信用卡号码
		creditcard: function( value, element ) {
			if ( this.optional( element ) ) {
				return "dependency-mismatch";
			}
			if ( /[^0-9 \-]+/.test( value ) ) {
				return false;
			}
			var nCheck = 0,
				nDigit = 0,
				bEven = false,
				n, cDigit;

			value = value.replace( /\D/g, "" );
			if ( value.length < 13 || value.length > 19 ) {
				return false;
			}

			for ( n = value.length - 1; n >= 0; n--) {
				cDigit = value.charAt( n );
				nDigit = parseInt( cDigit, 10 );
				if ( bEven ) {
					if ( ( nDigit *= 2 ) > 9 ) {
						nDigit -= 9;
					}
				}
				nCheck += nDigit;
				bEven = !bEven;
			}
			return ( nCheck % 10 ) === 0;
		},
		// 最小长度
		minlength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength( value, element );
			return this.optional( element ) || length >= param;
		},
		// 最大长度
		maxlength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength( value, element );
			return this.optional( element ) || length <= param;
		},
		// 指定长度
		rangelength: function( value, element, param ) {
			var length = $.isArray( value ) ? value.length : this.getLength( value, element );
			return this.optional( element ) || ( length >= param[ 0 ] && length <= param[ 1 ] );
		},
		// 最小值
		min: function( value, element, param ) {
			return this.optional( element ) || value >= param;
		},
		// 最大值
		max: function( value, element, param ) {
			return this.optional( element ) || value <= param;
		},
		// 指定区间数值
		range: function( value, element, param ) {
			return this.optional( element ) || ( value >= param[ 0 ] && value <= param[ 1 ] );
		},
		// 验证两次输入值是否相同
		equalTo: function( value, element, param ) {
			var target = $( param );
			if(target.length<=0) return true;
			if ( this.settings.onfocusout ) {
				target.unbind( ".validate-equalTo" ).bind( "blur.validate-equalTo", function() {
					$( element ).valid();
				});
			}
			return value === target.val();
		},
		// 服务器验证
		remote: function( value, element, param ) {
			if ( this.optional( element ) ) {
				return "dependency-mismatch";
			}
			var previous = this.previousValue( element ),
				validator, data;

			if (!this.settings.messages[ element.name ] ) {
				this.settings.messages[ element.name ] = {};
			}
			previous.originalMessage = this.settings.messages[ element.name ].remote;
			this.settings.messages[ element.name ].remote = previous.message;
			param = typeof param === "string" && { url: param } || param;

			if ( previous.old === value ) {
				return previous.valid;
			}
			previous.old = value;
			validator = this;
			this.startRequest( element );
			data = {};
			data[element.name] = value;
			data['_validate_'] = element.name;
			$.ajax( $.extend( true, {
				url: param,
				mode: "abort",
				port: "validate" + element.name,
				dataType: "json",
				type: "post",
				global: false,
				data: data,
				context: validator.currentForm,
				success: function( response ) {
					var valid = response === true || response === "true",
						errors, message, submitted;

					validator.settings.messages[ element.name ].remote = previous.originalMessage;
					if ( valid ) {
						submitted = validator.formSubmitted;
						validator.prepareElement( element );
						validator.formSubmitted = submitted;
						validator.successList.push( element );
						delete validator.invalid[ element.name ];
						validator.showErrors();
					} else {
						errors = {};
						message = response || validator.defaultMessage( element, "remote" );
						errors[ element.name ] = previous.message = $.isFunction( message ) ? message( value ) : message;
						validator.invalid[ element.name ] = true;
						validator.showErrors( errors );
					}
					previous.valid = valid;
					validator.stopRequest( element, valid );
				}
			}, param ) );
			return "pending";
		}
	}
});

var pendingRequests = {},
	ajax;
// Use a prefilter if available (1.5+)
if ( $.ajaxPrefilter ) {
	$.ajaxPrefilter(function( settings, _, xhr ) {
		var port = settings.port;
		if ( settings.mode === "abort" ) {
			if ( pendingRequests[port] ) {
				pendingRequests[port].abort();
			}
			pendingRequests[port] = xhr;
		}
	});
} else {
	// Proxy ajax
	ajax = $.ajax;
	$.ajax = function( settings ) {
		var mode = ( "mode" in settings ? settings : $.ajaxSettings ).mode,
			port = ( "port" in settings ? settings : $.ajaxSettings ).port;
		if ( mode === "abort" ) {
			if ( pendingRequests[port] ) {
				pendingRequests[port].abort();
			}
			pendingRequests[port] = ajax.apply(this, arguments);
			return pendingRequests[port];
		}
		return ajax.apply(this, arguments);
	};
}

// provides delegate(type: String, delegate: Selector, handler: Callback) plugin for easier event delegation
// handler is only called when $(event.target).is(delegate), in the scope of the jquery-object for event.target

$.extend($.fn, {
	validateDelegate: function( delegate, type, handler ) {
		return this.bind(type, function( event ) {
			var target = $(event.target);
			if ( target.is(delegate) ) {
				return handler.apply(target, arguments);
			}
		});
	}
});

}));