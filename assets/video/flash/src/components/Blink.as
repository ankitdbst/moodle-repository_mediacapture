package components{
	
	import flash.events.TimerEvent;
	import flash.utils.Timer;
	
	import mx.controls.Text;

	public class Blink extends Text {
		private var blinkTimer:Timer;
		public var displayed:Boolean;
		
		public function Blink():void {			
			this.blinkTimer = new Timer( 1000 , 0 );
            this.blinkTimer.addEventListener( "timer" , toggleText );
            this.blinkTimer.start();
            this.visible=false;
		}
		
		public function toggleText( event:TimerEvent ):void {
			if (displayed==false) {
				this.visible=false;
				return;
			}
			if( this.visible ){			
				this.visible = false;				
			}else{			
				this.visible = true;				
			}		
		}
		
		
		public function get interval():uint	{
			return this.blinkTimer.delay;
		}
		public function set interval( value:uint ):void {
			this.blinkTimer.delay = value;		
		}
				
	}
	
}