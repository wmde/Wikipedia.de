<footer class="page-footer" id="donationfooter">
	<div id="confetti-container">
		<canvas id="confetti"></canvas>
	</div>
	<div class="page-footer-column">
		<a href="https://www.wikimedia.de/wikipedia20" class="anniversary">

			<div class="anniversary-cards">
				<div class="anniversary-card x-1 y-1" data-x="x-1" data-y="y-1"></div>
				<div class="anniversary-card x-2 y-2" data-x="x-2" data-y="y-2"></div>
				<div class="anniversary-card x-3 y-3" data-x="x-3" data-y="y-3"></div>
				<div class="anniversary-card x-4 y-4" data-x="x-4" data-y="y-4"></div>
			</div>
			
			<img src="img/Wikipedia_20.png" alt="Wikipedia 20" class="anniversary-logo">
			<span class="anniversary-cta">→ 20 Jahre Wikipedia</span>
		</a>
	</div>
	<div class="page-footer-column">
		<div class="link-block">
			<ul>
				<li>
					<p class="wikimedia-logo-footer-container">
						<a href="https://www.wikimedia.de" class="wikimedia-logo-footer">
							<img src="img/wmde_logo_footer.svg" alt="Wikimedia Deutschland e.V.">
						</a>
						<strong>Wikimedia Deutschland e. V.</strong>
					</p>
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
	</div>
</footer>

<script>
	const xClasses = [ 'x-1', 'x-2', 'x-3', 'x-4' ];
	const yClasses = [ 'y-1', 'y-2', 'y-3', 'y-4' ];

	function randomInt( max ) {
		return Math.floor( Math.random() * Math.floor( max ) );
	}

	function randomiseBackground( $this ) {
		const timeout = 1000 + randomInt( 1000 );

		setInterval( function() {
			const currentX = $this.attr( 'data-x' );
			const currentY = $this.attr( 'data-y' );
			const newX = xClasses[ randomInt( 4 ) ];
			const newY = yClasses[ randomInt( 4 ) ];

			$this.removeClass( currentX )
				.removeClass( currentY )
				.addClass( newX )
				.addClass( newY )
				.attr( 'data-x', newX )
				.attr( 'data-y', newY );
		}, timeout);
	}

	$( document ).ready( function() {
		$( '.anniversary-card' ).each( function() {
			randomiseBackground( $( this ) );
		} );
	} );

	(function () {
		// globals
		let canvas;
		let container;
		let ctx;
		let W;
		let H;
		const mp = 60; //max particles
		let particles = [];
		let angle = 0;
		let tiltAngle = 0;
		let confettiActive = true;
		let animationComplete = true;
		let animationHandler;

		const particleColors = {
			colorOptions: [ "DodgerBlue", "OliveDrab", "Gold", "pink", "SlateBlue", "lightblue", "Violet", "PaleGreen", "SteelBlue", "SandyBrown", "Chocolate", "Crimson" ],
			colorIndex: 0,
			colorIncrementer: 0,
			colorThreshold: 10,
			getColor: function() {
				if( this.colorIncrementer >= 1 ) {
					this.colorIncrementer = 0;
					this.colorIndex++;
					if( this.colorIndex >= this.colorOptions.length ) {
						this.colorIndex = 0;
					}
				}
				this.colorIncrementer++;
				return this.colorOptions[ this.colorIndex ];
			}
		};

		function ConfettiParticle(color) {
			this.x = Math.random() * W; // x-coordinate
			this.y = (Math.random() * H) - H; //y-coordinate
			this.r = RandomFromTo(10, 30); //radius;
			this.d = (Math.random() * mp) + 10; //density;
			this.color = color;
			this.tilt = Math.floor(Math.random() * 10) - 10;
			this.tiltAngleIncremental = (Math.random() * 0.07) + .05;
			this.tiltAngle = 0;

			this.draw = function () {
				ctx.beginPath();
				ctx.lineWidth = this.r / 2;
				ctx.strokeStyle = this.color;
				ctx.moveTo(this.x + this.tilt + (this.r / 4), this.y);
				ctx.lineTo(this.x + this.tilt, this.y + this.tilt + (this.r / 4));
				return ctx.stroke();
			}
		}

		$(document).ready(function () {
			SetGlobals();
			InitializeConfetti();

			$(window).resize(function () {
				W = window.innerWidth;
				H = window.innerHeight;
				canvas.width = W;
				canvas.height = H;
			});

		});

		function SetGlobals() {
			canvas = document.getElementById("confetti");
			container = document.getElementById("confetti-container");
			ctx = canvas.getContext("2d");
			W = container.offsetWidth;
			H = container.offsetHeight;
			canvas.width = W;
			canvas.height = H;
		}

		function InitializeConfetti() {
			particles = [];
			animationComplete = false;
			for ( let i = 0; i < mp; i++) {
				const particleColor = particleColors.getColor();
				particles.push(new ConfettiParticle(particleColor));
			}
			StartConfetti();
		}

		function Draw() {
			ctx.clearRect(0, 0, W, H);
			const results = [];
			for ( let i = 0; i < mp; i++) {
				(function (j) {
					results.push(particles[j].draw());
				})(i);
			}
			Update();

			return results;
		}

		function RandomFromTo(from, to) {
			return Math.floor(Math.random() * (to - from + 1) + from);
		}


		function Update() {
			let remainingFlakes = 0;
			let particle;
			angle += 0.01;
			tiltAngle += 0.1;

			for ( let i = 0; i < mp; i++) {
				particle = particles[i];
				if (animationComplete) return;

				if (!confettiActive && particle.y < -15) {
					particle.y = H + 100;
					continue;
				}

				stepParticle(particle, i);

				if (particle.y <= H) {
					remainingFlakes++;
				}
				CheckForReposition(particle, i);
			}

			if (remainingFlakes === 0) {
				StopConfetti();
			}
		}

		function CheckForReposition(particle, index) {
			if ((particle.x > W + 20 || particle.x < -20 || particle.y > H) && confettiActive) {
				if (index % 5 > 0 || index % 2 === 0) //66.67% of the flakes
				{
					repositionParticle(particle, Math.random() * W, -10, Math.floor(Math.random() * 10) - 20);
				} else {
					if (Math.sin(angle) > 0) {
						//Enter from the left
						repositionParticle(particle, -20, Math.random() * H, Math.floor(Math.random() * 10) - 20);
					} else {
						//Enter from the right
						repositionParticle(particle, W + 20, Math.random() * H, Math.floor(Math.random() * 10) - 20);
					}
				}
			}
		}
		function stepParticle(particle, particleIndex) {
			particle.tiltAngle += particle.tiltAngleIncremental;
			particle.y += (Math.cos(angle + particle.d) + 3 + particle.r / 2) / 2;
			particle.x += Math.sin(angle);
			particle.tilt = (Math.sin(particle.tiltAngle - (particleIndex / 3))) * 15;
		}

		function repositionParticle(particle, xCoordinate, yCoordinate, tilt) {
			particle.x = xCoordinate;
			particle.y = yCoordinate;
			particle.tilt = tilt;
		}

		function StartConfetti() {
			W = container.offsetWidth;
			H = container.offsetHeight;
			canvas.width = W;
			canvas.height = H;
			(function animloop() {
				if (animationComplete) return null;
				animationHandler = requestAnimFrame(animloop);
				return Draw();
			})();
		}

		function StopConfetti() {
			animationComplete = true;
			if (ctx === undefined) return;
			ctx.clearRect(0, 0, W, H);
		}

		window.requestAnimFrame = (function () {
			return window.requestAnimationFrame ||
				window.webkitRequestAnimationFrame ||
				window.mozRequestAnimationFrame ||
				window.oRequestAnimationFrame ||
				window.msRequestAnimationFrame ||
				function (callback) {
					return window.setTimeout(callback, 100);
				};
		})();
	})();

</script>