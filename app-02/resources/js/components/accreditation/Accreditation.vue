<template>
	<div class="form-accr">
		<div class="form__head">
			<a href="#" class="form__logo">
				<img src="images/logo.png" alt="">
			</a><!-- /.form__logo -->

			<div class="form__steps-nav">
				<ul>
					<li v-for="(step, index) in steps" :class="{ 'current' : currentStep === index }" :key="index">
						<h5>{{ step.name }}</h5>
						
						<p>{{ step.text }}</p>
					</li>
				</ul>
			</div><!-- /.form__steps-nav -->

			<div class="form__contacts">
				<a href="tel:8554258686">
					<img src="images/ico-call.svg" alt="">

					(855) 425-8686
				</a>
			</div><!-- /.form__contacts -->
		</div><!-- /.form__head -->

		<div class="form__body">
			<form>
				<intro
					v-if="currentStep === 0"
					@next="nextStep"
				/>

				<quiz
					v-show="currentStep === 1"
					v-model=questions
					title="Part I: Replacing R-22 with Blends"
					text="Learn how the R-22 phaseout is forcing change in the HVAC-R industry."
					video="https://www.youtube.com/embed/NpEaa2P7qZI"
					offset="0"
					nextButtonText="Continue to Part II &rarr;"
					@prev="prevStep"
					@next="nextStep"
				/>

				<quiz
					v-show="currentStep === 2"
					v-model=questions
					title="Part II: Using Bluon TdX 20"
					text="Learn how the R-22 phaseout is forcing change in the HVAC-R industry."
					video="https://www.youtube.com/embed/NpEaa2P7qZI"
					offset="10"
					nextButtonText="Continue to Registration &rarr;"
					@prev="prevStep"
					@next="nextStep"
				/>

				<registration-form
					v-show="currentStep === 3"
					@prev="prevStep"
					@next="nextStep"
				/>

				<complete
					v-show="currentStep === 4"
				/>
				
			</form>
		</div><!-- /.form__body -->
	</div><!-- /.form-accr -->
</template>
<script>
	/**
	 * Internal Dependencies
	 */
	import Intro from './Intro';
	import Quiz from './Quiz';
	import RegistrationForm from './RegistrationForm';
	import Complete from './Complete';

	export default {
		components: {
			Intro,
			Quiz,
			RegistrationForm,
			Complete,
		},

		data() {
			return {
				steps: [
					{
						name: 'Intro',
						text: '1 min',
					},
					{
						name: 'Part I',
						text: '10 min',
					},
					{
						name: 'Part II',
						text: '10 min',
					},
					{
						name: 'Account',
						text: '2 min',
					},
					{
						name: 'Done!',
						text: 'You\'re Accredited!',
					},
				],
				currentStep: 0,
				questions: [
					{
						index: 0,
						title: '1. No more R-22 will be imported into the US starting:',
						options: [
							'January 2020',
							'January 2021',
							'January 2022',
							'January 2023',
						],
						correctIndex: 0
					},

					{
						index: 1,
						title: '2. How are blends different than a single constituent refrigerant such as R-22?',
						options: [
							'Blends are a combination of two or more compounds',
							'Each individual constituent has a unique boiling point (pressure & temp)',
							'Combining the unique boiling points gives blends their “Glide”',
							'All of the above',
						],
						correctIndex: 3
					},


					{
						index: 2,
						title: '3. All Zeotropic blends have a glide.',
						options: [
							'True',
							'False',
						],
						correctIndex: 0
					},

					{	
						index: 3,
						title: '4. A well designed glide (blend) gives you:',
						options: [
							'More work performed through chemistry',
							'Less work done by the compressor',
							'Less amps drawn on the compressor',
							'All of the above',
						],
						correctIndex: 3
					},

					{	
						index: 4,
						title: '5. Because blends have different pressures, Superheat & Subcool are critical.',
						options: [
							'True',
							'False',
						],
						correctIndex: 0
					},

					{	
						index: 5,
						title: '6. Pre-existing issues like low airflow, broken crank case heaters, and improper oil levels will be MAGNIFIED if not fixed before installing a replacement refrigerant.',
						options: [
							'True',
							'False',
						],
						correctIndex: 0
					},

					{	
						index: 6,
						title: '7. Since blends operate at different pressures, it’s VERY important to charge systems to proper:',
						options: [
							'Name plate charge',
							'Compressor amperage',
							'Superheat and Subcool',
							'All of the above',
						],
						correctIndex: 2
					},

					{	
						index: 7,
						title: '8. Electronic Expansion Valves should be programmed with the R-458A profile.',
						options: [
							'True',
							'False',
						],
						correctIndex: 0
					},

					{	
						index: 8,
						title: '9. If the refrigerant blend is below the temperature that corresponds to the liquid pressure, it is _____. If it is above the temperature that corresponds to the vapor pressure, it is _____.',
						options: [
							'Subcooled (fully liquid), Superheated (fully vapor)',
							'Superheated (fully vapor), Subcooled (fully liquid)',
							'Saturated (partially liquid and vapor), Subcooled (fully liquid)',
							'Superheated (fully vapor), Saturated (partially liquid and vapor)',
						],
						correctIndex: 0
					},

					{	
						index: 9,
						title: '10. In general, leaks less than _____ can be topped off.',
						options: [
							'10%',
							'20%',
							'50%',
							'90%',
						],
						correctIndex: 1
					},

					{	
						index: 10,
						title: '11. What is the primary consideration when converting to TdX 20?',
						options: [
							'TdX 20 operates at higher pressures',
							'TdX 20 operates at lower pressures',
							'TdX 20 operates at warmer temperatures',
							'TdX 20 operates exactly like R-22',
						],
						correctIndex: 1
					},

					{	
						index: 11,
						title: '12. The first step to converting an R-22 system is to _____.',
						options: [
							'Establish a baseline of proper system performance per OEM guide',
							'Recover the refrigerants',
							'Evacuate the system',
							'Charge the system to 80%',
						],
						correctIndex: 0
					},

					{	
						index: 12,
						title: '13. When baselining proper system performance, you should address:',
						options: [
							'Proper airflow (generally 400 CFM/ton)',
							'Proper pressures and temperatures',
							'Proper Superheat and Subcool',
							'All the above',
						],
						correctIndex: 3
					},

					{	
						index: 13,
						title: '14. Always charge a blend as a _____ ONLY.',
						options: [
							'Vapor',
							'Gas',
							'Liquid',
							'None of the above',
						],
						correctIndex: 2
					},

					{	
						index: 14,
						title: '15. Systems with adjustable metering devices should be _____.',
						options: [
							'Charged to Superheat, tuned to Subcool',
							'Charged to suction pressure, tuned to discharge pressure',
							'Charged to Subcool, tuned to Superheat',
							'None of the above',
						],
						correctIndex: 2
					},

					{	
						index: 15,
						title: '16. Adjustable metering devices (TXVs) are typically tuned/adjusted:',
						options: [
							'8-10 turns closed',
							'8-10 turns open',
							'1-4 turns open',
							'1-4 turns closed',
						],
						correctIndex: 3
					},

					{	
						index: 16,
						title: '17. What is TdX 20’s vapor pressure with a 40°F evaporator outlet temperature?',
						chart: true,
						options: [
							'62.8 Psig',
							'40.4 Psig',
							'50.5 Psig',
							'118.2 Psig',
						],
						correctIndex: 2
					},

					{	
						index: 17,
						title: '18. What is TdX 20’s liquid pressure with a 105°F condenser?',
						chart: true,
						options: [
							'208.1 Psig',
							'215.8 Psig',
							'254.2 Psig',
							'339.9 Psig',
						],
						correctIndex: 0
					},

					{	
						index: 18,
						title: '19. What is the Superheat of a system with R-458A operating at a suction pressure of 50.5 Psig and a suction line temp of 65°F?',
						chart: true,
						options: [
							'15°F',
							'20°F',
							'25°F',
							'30°F',
						],
						correctIndex: 2
					},

					{	
						index: 19,
						title: '20. What is the Subcool of a system with R-458A operating with a liquid line pressure 208.1 Psig and a liquid line temp of 95°F?',
						chart: true,
						options: [
							'5°F',
							'10°F',
							'15°F',
							'20°F',
						],
						correctIndex: 1
					},
				],
			}
		},

		methods: {
			/**
	         * Handle Next Step
	         */
			nextStep() {
				this.currentStep++;
			},

			/**
	         * Handle Prev Step
	         */
			prevStep() {
				this.currentStep--;
			}
		}
	}
</script>