/**
 * Gauge Component
 * 
 * This example Flex 2 component shows how to build a component from scratch.
 * 
 * This class is the one developers will use in MXML and ActionScript files:
 * 
 * 		<g:Gauge [options...] xmlns:g="gauge.*" />
 * 
 * Properties:
 * 
 * 	minimum:	The minimum value for the gauge. Default: 0
 *  maximum:	The maximum value for the gauge. Default: 100
 *	value:		The current value of the gauge, between the minimum and 
 * 				maximum values. Default: 0
 */			
package components.gauge
{
	import flash.display.DisplayObject;
	
	import components.gauge.skins.*;
	import components.gauge.events.GaugeEvent;
	
	import mx.core.IFlexDisplayObject;
	import mx.core.UIComponent;
	import mx.effects.Rotate;
	import mx.skins.Border;
	import mx.skins.ProgrammaticSkin;
	import mx.skins.RectangularBorder;
	import mx.styles.CSSStyleDeclaration;
	import mx.styles.ISimpleStyleClient;
	import mx.styles.StyleManager;
	import flash.events.MouseEvent;
	import flash.filters.DropShadowFilter;
	import flash.events.Event;

	/* frameSkin
	 * The skin class for the background of the gauge. The default class
	 * is gauge.skins.programmatic.GaugeFrameSkin.
	 */
	[Style(name="frameSkin",type="Class",inherit="no")]
	
	/* coverSkin
	 * The skin class for the needle cover. The default class is
	 * gauge.skins.programmatic.GaugeCoverSkin.
	 */
	[Style(name="coverSkin",type="Class",inherit="no")]
	
	/* needleSkin
	 * The skin class for the needle. The default class is
	 * gauge.skins.programmatic.GaugeNeedleSkin.
	 */
	[Style(name="needleSkin",type="Class",inherit="no")]
	
	/* backgroundColor
	 * The color for the background of the frame. Default: white
	 */
	[Style(name="backgroundColor",type="Number",format="Color",inherit="no")]
	
	/* backgroundAlpha
	 * The transparency of the background of the frame. Default: 1
	 */
	[Style(name="backgroundAlpha",type="Number",inherit="no")]
	
	/* borderColor
	 * The color for the border of the frame. Default: dark gray
	 */
	[Style(name="borderColor",type="Number",format="Color",inherit="no")]
	
	/* borderAlpha
	 * The transparency of the border of the frame. Default: 1
	 */
	[Style(name="borderAlpha",type="Number",inherit="no")]
	
	/* borderThickness
	 * The thickness of the frame border. Default: 1
	 */
	[Style(name="borderThickness",type="Number",nherit="no")]
	
	/* needleColor
	 * The color of the needle. Default: black
	 */
	[Style(name="needleColor",type="Number",format="Color",inherit="no")]
	
	/* needleThickness
	 * The thickness of the needle. Default: 3
	 */
	[Style(name="needleThickness",type="Number",inherit="no")]
	
	/* needleAlpha
	 * The transparency of the needle. Default 1
	 */
	[Style(name="needleAlpha",type="Number",inherit="no")]
	
	/* coverColor
	 * The color of the needle cover. Default: dark gray
	 */
	[Style(name="coverColor",type="Number",format="Color",inherit="no")]
	
	/* coverAlpha
	 * The transparency of the cover. Default 1
	 */
	[Style(name="coverAlpha",type="Number",inherit="no")]
	
	/* coverDropShadowEnabled 
	* Indicates of the cover should have a shadow (false) or not (true).
	* Default: true (shadow)
	*/
	[Style(name="coverDropShadowEnabled",type="Boolean",inherit="no",default="true")]
	
	/* gaugeClick (event)
	 * Fired when the mouse is clicked over the gauge. The value property of the
	 * GaugeEvent contains the approximate value between the minimum and maximum
	 * of the Gauge values.
	 */
	[Event(name="gaugeClick",type="components.gauge.events.GaugeEvent")]
	
	public class Gauge extends UIComponent
	{		
		// Variables holding the skin instances
		protected var frameSkin:IFlexDisplayObject;
		protected var coverSkin:IFlexDisplayObject;
		protected var needleSkin:IFlexDisplayObject;
		
		// Single Rotate effect used to move the needle.
		private var rotate:Rotate;
		
		/**
		 * Constructor
		 * 
		 */
		public function Gauge()
		{
			super();
			
			// and handlers for mouse events to make the component
			// interactive. Add event handlers for keyboard, too.
			addEventListener( MouseEvent.CLICK, clickHandler );
			addEventListener( MouseEvent.MOUSE_DOWN, mouseHandler );
			addEventListener( MouseEvent.MOUSE_UP, mouseHandler );
			addEventListener( MouseEvent.MOUSE_MOVE, mouseHandler );
			addEventListener( MouseEvent.MOUSE_OUT, mouseHandler );
		}
		
		/**
		 * createChildren
		 * 
		 * This method is invoked by the Flex framework when it is time for
		 * the component to create any children. In this case, it is time
		 * to create the skins.
		 */
		override protected function createChildren():void
		{
			super.createChildren();
			
			frameSkin  = createSkin( "frameSkin", GaugeSkin ); 
			needleSkin = createSkin( "needleSkin", GaugeSkin ); 
			coverSkin  = createSkin( "coverSkin", GaugeSkin ); 
			
			rotate = new Rotate(needleSkin);
		}
		
		/**
		 * createSkin
		 * 
		 * Creates the given skin. The skin will either have been specified
		 * by the skin style or, if not present, the skin will default to
		 * the one given.
		 * 
		 */
		protected function createSkin( skinName:String, defaultSkin:Class ) : IFlexDisplayObject
		{
			// Look up the skin by its name to see if it is already created. Note
			// below where addChild() is called; this makes getChildByName possible.
			var newSkin:IFlexDisplayObject =
				IFlexDisplayObject(getChildByName(skinName));
				
			// if the skin needs to be created it will be null...
			
			if (!newSkin)
			{
				// Attempt to get the class for the skin. If one has not been supplied
				// by a style, use the default skin.
				
				var newSkinClass:Class = Class(getStyle(skinName));
				if( !newSkinClass ) newSkinClass = defaultSkin;
				
				if (newSkinClass)
				{
					// Create an instance of the class.
					newSkin = IFlexDisplayObject(new newSkinClass());
					if( !newSkin ) newSkin = new defaultSkin();
					
					// Set its name so that we can find it in the future
					// using getChildByName().
					newSkin.name = skinName;

					// Make the getStyle() calls in the skin class find the styles
					// for this Gauge instance. In other words, but setting the styleName
					// to 'this' it allows the skin to query the component for styles. For
					// example, when the skin code does getStyle('backgroundColor') it 
					// retrieves the style from this Gauge and not from the skin.
					var styleableSkin:ISimpleStyleClient = newSkin as ISimpleStyleClient;
					if (styleableSkin)
						styleableSkin.styleName = this;
	
					// Make sure the skin is a proper child of the component
					addChild(DisplayObject(newSkin));
				}
			}
			
			return newSkin;
		}
		
		/**
		 * measure
		 * 
		 * Define the default size of the component. Here the minimum size
		 * will be 50x50.
		 */
        override protected function measure():void 
        {
            super.measure();

            measuredWidth = measuredMinWidth = 50;
            measuredHeight = measuredMinHeight = 50;
        }
        
        // This component uses a RotateEffect to position the needle; _prevAngle
        // holds the last known value.
        private var _prevAngle:Number = 135;
        
        /**
        * updateDisplayList
        * 
        * Draws the skin and its elements. This method is called by the Flex 2 framework
        * at the appropriate time. Never call this method directly. You can indicate the
        * need for it to be called by using invalidateDisplayList().
        */
        override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void
        {
        	super.updateDisplayList(unscaledWidth,unscaledHeight);
        	
        	var coverDropShadowEnabled:Object = getStyle("coverDropShadowEnabled");
        	
        	// positin and size the skins.
        	frameSkin.move(0,0);
        	frameSkin.setActualSize(unscaledWidth,unscaledHeight);
        	
        	// the needle skin has its origin moved to the very center
        	// of the component; makes rotating it easy.
        	needleSkin.move(0,0);
        	needleSkin.setActualSize(unscaledWidth,unscaledHeight);
        	
        	// the cover has its origin moved to the center of the 
        	// component, too.
        	coverSkin.move( 0, 0 );
        	coverSkin.setActualSize( unscaledWidth, unscaledHeight );
        	if( coverDropShadowEnabled == null || coverDropShadowEnabled == true ) {
	        	DisplayObject(coverSkin).filters = [ new DropShadowFilter(4,45,0,.5) ];
	        }
	        
	        // adjust the value to make sure it is within bounds.
        	if( _value < _minimum ) _value = _minimum;
        	if( _value > _maximum ) _value = _maximum;
        	
        	// determine the angle of the needle based on the current
        	// value and minimum and maximum values.
        	var angle:Number = calculateAngleFromValue(_value);
        	
        	// Use a Rotate effect to spin the needle from its previous
        	// position.

        	// if( rotate.isPlaying ) rotate.end();
        	// rotate.angleFrom = _prevAngle;
        	// rotate.angleTo = angle;
        	// rotate.originX = 0;
        	// rotate.originY = 0;
        	// rotate.play();
			coverSkin.width = _value*width/100;
			coverSkin.height = (height*coverSkin.width)/width;
			coverSkin.y = height-coverSkin.height;
			trace("widht = "+width);
			trace("height = "+height);
        	
        	_prevAngle = angle;
        }
        
        /**
        * calculateAngleFromValue
        * 
        * Determines the angle of the needle based on the value
        * and minimum and maximum properties.
        * 
        * Note: it is tempting to put the two statements of this function
        * directly into the updateDisplayList function. However, should someone
        * want to extend this class, they can use this method to do the same
        * calculation from the extended class' updateDisplayList.
        */
        protected function calculateAngleFromValue(v:Number) : Number
        {
        	var p:Number = (v-_minimum)/(_maximum-_minimum);
        	var angle:Number = 270*p + 135;
        	
        	return angle;
        }
        
        /*
         * INTERACTIVITY
         *
         * The code in this section makes it possible to move the gauge needle using
         * mouse events. You can extend this to keyboard events if that makes sense.
         *
         *
         */
		
		// isDown is a flag to make sure no mouse motion events are sent if the mouse
		// button isn't already down.
		private var isDown:Boolean = false;
		
		/**
		 * clickHandler
		 * 
		 * This clickHandler is detecting a mouseClick on the component. This is then
		 * converted into a GaugeEvent and dispatched.
		 * 
		 * Note that the needle is NOT moved here. Rather its new position is calculated
		 * and invalidateDisplayList is called to flag the update. 
		 */
		private function clickHandler( event:flash.events.MouseEvent ) : void
		{
			// we don't really want click events to come from this control.
			event.stopImmediatePropagation();
			
			// calculate the angle of the mouse click with respect to the center
			// of the component.
			// var xpos:Number = event.localX - width/2;
			// var ypos:Number = event.localY - height/2;
			// var radius:Number = Math.sqrt( xpos*xpos + ypos*ypos );
			// var radianSin:Number = 0;
			// var radianCos:Number = 0;
			// if( radius > 0 ) {
				// radianCos = Math.acos( ypos/radius );
				// radianSin = Math.asin( xpos/radius );
			// }
			// var angle:Number = radianCos*180/Math.PI;
			// if( radianSin > 0 ) angle = 360 - angle;
			
			// now compute the value based on the angle and the min and max values
			// given to the component.
			var newValue:Number = -1;
			// if( angle >= 45 && angle <= 315 ) {
				// newValue = (angle-45)/270*(_maximum-_minimum)+_minimum;
			// }
			newValue = event.localX*100/(width);
	        // adjust the value to make sure it is within bounds.
        	if( newValue < _minimum ) newValue = _minimum;
        	if( newValue > _maximum ) newValue = _maximum;
			
			// create the event and dispatch it.
			var gEvent:GaugeEvent = new GaugeEvent( value, newValue, event.localX, event.localY );
			dispatchEvent( gEvent );
			
			// if liveDragging, update the value and invalidate the display list
			// to reposition the needle.
			if( _liveDragging ) value = newValue;
			trace("liveDragging = "+_liveDragging+"; value = "+value);
			invalidateDisplayList();
		}
		
		/**
		 * mouseHandler
		 * 
		 * This is the event handler for mouse events as set in the component's constructor
		 * function. This reuses the clickHandler for the calculations and repositioning
		 * of the needle.
		 */
		private function mouseHandler( event:flash.events.MouseEvent ) : void
		{
			if( event.type == MouseEvent.MOUSE_DOWN && _liveDragging ) {
				isDown = true;
				clickHandler( event );
			}
			else if( event.type == MouseEvent.MOUSE_UP || event.type == MouseEvent.MOUSE_OUT ) isDown = false;
			else if( event.type == MouseEvent.MOUSE_MOVE && isDown && _liveDragging ) {
				clickHandler( event );
			}
		}
        
        /*
         * PROPERTIES
         *
         * When a property is set, its value is copied to the class variable (eg,
         * _value) and then invalidateDisplayList is called. This allows the Flex framework
         * to call updateDisplayList at the proper time. For example, it is possible
         * to set a property before there are any graphics present; calling updateDisplayList
         * then would lead to an error.
         */
        
        /**
        * value
        * 
        * The value of the gauge; guaranteed to be between the minimum
        * and maximum values.
        */
        private var _value:Number = _maximum;
        
        [Bindable(event="valueChanged")]
        public function get value() : Number
        {
        	return _value;
        }
        
        public function set value( n:Number ) : void
        {
        	_value = n;
        	invalidateDisplayList();
        	dispatchEvent( new Event("valueChanged") );
        }
        
        /**
        * minimum
        * 
        * The smallest allowed value for the gauge; default=0. If you think
        * of the gauge's face as a clock, the minimum is mapped to the 8 o'clock
        * position.
        */
        private var _minimum:Number = 0;
        
        [Bindable]
        public function get minimum() : Number
        {
        	return _minimum;
        }
        
        public function set minimum( n:Number ) : void
        {
        	_minimum = n;
        	invalidateDisplayList();
        }
        
        /**
        * maximum
        * 
        * The largest allowed value for the gauge; default=100. The maximum
        * value is mapped to the 4 o'clock position.
        */
        private var _maximum:Number = 100;

		[Bindable]
		public function get maximum() : Number
		{
			return _maximum;
		}
		
        public function set maximum( n:Number ) : void
        {
        	_maximum = n;
        	invalidateDisplayList();
        }
		
		/**
		 * liveDragging
		 * 
		 * Sets a flag to indicate that the needle should track the mouse.
		 */
		private var _liveDragging:Boolean = false;
		
		[Bindable]
		public function get liveDragging() : Boolean
		{
			return _liveDragging;
		}
		
		public function set liveDragging( b:Boolean ) : void
		{
			_liveDragging = b;
		}
		
	}
}