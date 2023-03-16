<template>
	<div class="form-step-one">	
		<div class="form__content">
			<div class="form__shell">
				<div class="form__title">
					<h1>Welcome HVAC Techs!</h1>
				</div><!-- /.form__title -->

				<div class="form__section">
					<div class="form__cols">
						<div class="form__entry">
							<h5>The Bluon Accreditation Program</h5>

							<p>Welcome to Bluon’s 20-minute crash course on replacing R-22 and refrigerant blends. Complete this course to unlock Tier I status and gain access to our mobile app, 24/7 support hotline, more.</p>
						</div><!-- /.form__entry -->

						<div class="form__image">
							<img src="images/web-img-1.svg" alt="">
						</div><!-- /.form__image -->
					</div><!-- /.form__cols -->
				</div><!-- /.form__section -->

				<div class="form__section">
					<div class="form__video">
						<iframe width="560" height="315" src="https://www.youtube.com/embed/NpEaa2P7qZI" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div><!-- /.form__video -->
				</div><!-- /.form__section -->
			</div><!-- /.form__shell -->
		</div><!-- /.form__content -->

		<div class="form__actions">
			<div class="form__shell">
				<a href="https://bluonenergy.com" class="btn-text">Bluon Home</a>

				<a href="#" class="btn-form" @click.prevent="$emit('next')">Get Accredited Now &rarr;</a>
			</div><!-- /.form__shell -->
		</div><!-- /.form__actions -->
	</div><!-- /.form-step-one -->
</template>