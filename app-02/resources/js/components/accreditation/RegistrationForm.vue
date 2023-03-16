<template>
	<div class="form-step-four">
		<div class="form__content">
			<div class="form__shell">
				<div class="form__title">
					<h1>Bluon Account</h1>

					<p>Create your Bluon Account by providing some basic personal and work details.</p>
				</div><!-- /.form__title -->

				<div class="form__section">
					<div class="form-errors" v-if="formErrors">
						<p v-for="(error, index) in formErrors" :key="index">{{ error[0] }}</p>
					</div>
					<div class="form__group">
						<h5>Account Info</h5>

						<div class="form__row">
							<div class="form__col-1of2" :class="{ 'error': $v.account.firstName.$error }">
								<input type="text" placeholder="First Name" name="firstName" v-model="account.firstName">
								<span class="form__error">First Name is required</span>
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2" :class="{ 'error': $v.account.lastName.$error }">
								<input type="text" placeholder="Last Name" name="lastName" v-model="account.lastName">
								<span class="form__error">Last Name is required</span>
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of2" :class="{ 'error': $v.account.email.$error }">
								<input type="email" placeholder="Email" name="email" v-model="account.email">
								<span class="form__error">Email is required</span>
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2">
								<input type="text" placeholder="Mobile Phone" name="phone" v-model="account.phone">
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of2" :class="{ 'error': $v.account.password.$error }">
								<input type="password" placeholder="Password" name="password" v-model="account.password">
								<span class="form__error">Password is required</span>
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2" :class="{ 'error': $v.account.confirmPassword.$error }">
								<input type="password" placeholder="Repeat Password" name="confirmPassword" v-model="account.confirmPassword">
								<span class="form__error">Please confirm your password</span>
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->
					</div><!-- /.form__group -->

					<div class="form__group">
						<h5>Your Location</h5>

						<div class="form__row">
							<div class="form__col-1of1">
								<input type="text" placeholder="Mailing Address" name="address" v-model="account.address">
							</div><!-- /.form__col-1of1 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of2">
								<input type="text" placeholder="City" name="city" v-model="account.city">
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2">
								<input type="text" placeholder="State/Province" name="state" v-model="account.state">
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of2">
								<input type="text" placeholder="Postal Code" name="zip" v-model="account.zip">
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2">
								<div class="form__select">
									<select name="country" v-model="account.country">
										<option>Choose country</option>

										<option
											v-for="(country, index) in countries"
											:value="country.value"
											:key="index"
										>
											{{ country.label }}
										</option>
									</select>
								</div><!-- /.form__select -->
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->
					</div><!-- /.form__group -->

					<div class="form__group">
						<h5>Your Work Info</h5>

						<div class="form__row">
							<div class="form__col-1of1">
								<input type="text" placeholder="Company" name="company" v-model="account.company">
							</div><!-- /.form__col-1of1 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of2">
								<label>I am a:</label>

								<div class="form__select">
									<select name="occupation" v-model="account.occupation">
										<option value="technician">HVAC Technician</option>
										<option value="service-manager">Service Manager</option>
										<option value="sales-person">Sales Person</option>
										<option value="owner">Business Owner</option>
										<option value="supplier">HVAC Supplier</option>
										<option value="other">Other/NA</option>
									</select>
								</div><!-- /.form__select -->
							</div><!-- /.form__col-1of2 -->

							<div class="form__col-1of2">
								<label>We mostly do:</label>

								<div class="form__select">
									<select name="typeOfServices" v-model="account.typeOfServices">
										<option value="commercial">Commercial</option>
										<option value="residential">Residential</option>
										<option value="other">Other/NA</option>
									</select>
								</div><!-- /.form__select -->
							</div><!-- /.form__col-1of2 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of1">
								<label>Who’s your HVAC supplier?</label>

								<input type="text" placeholder="Supplier Company Name" name="supplier" v-model="account.supplier">
							</div><!-- /.form__col-1of1 -->
						</div><!-- /.form__row -->

						<div class="form__row">
							<div class="form__col-1of1">
								<div class="form__checkbox">
									<input type="checkbox" id="accept" name="terms" v-model="termsAccepted">

									<label for="accept">I accept the Bluon web and Mobile App <a href="https://www.bluonenergy.com/app-terms-of-use/" target="_blank">terms of service</a>.</label>

									<span v-show="$v.termsAccepted.$error">Please accept our terms of service</span>
								</div><!-- /.form__checkbox -->
							</div><!-- /.form__col-1of1 -->
						</div><!-- /.form__row -->
					</div><!-- /.form__group -->
				</div><!-- /.form__section -->
			</div><!-- /.form__shell -->
		</div><!-- /.form__content -->

		<div class="form__actions">
			<div class="form__shell">
				<a href="#" class="btn-form btn-form--back" @click.prevent="$emit('prev')">&larr; Back</a>

				<a href="#" class="btn-form" @click.prevent="handleSubmit">Submit &amp; Finish!</a>
			</div><!-- /.form__shell -->
		</div><!-- /.form__actions -->
	</div>
</template>
<script>
	/**
	 * The external dependencies
	 */
	import { required, sameAs } from 'vuelidate/lib/validators';

	export default {
		data() {
			return {
				countries: [],
				account: {
					firstName: '',
					lastName: '',
					email: '',
					password: '',
					confirmPassword: '',
					address: '',
					city: '',
					state: '',
					zip: '',
					phone: '',
					country: '',
					company: '',
					occupation: '',
					typeOfServices: '',
					supplier: '',
				},
				termsAccepted: '',
				formErrors: {},
			}
		},

		validations: {
			account: {
				firstName: {
					required,
				},
				lastName: {
					required,
				},
				email: {
					required,
				},
				password: {
					required,
				},
				confirmPassword: {
					required,
					sameAsPassword: sameAs('password')
				},
			},
			termsAccepted: {
				required,
				sameAs: sameAs( () => true )
			}
		},

		methods: {
			/**
			 * Get countries list
			 */
			getCountries() {
				axios.get('api/v2/countries')
					.then(response => this.countries = response.data)
					.catch(e => {console.log(e)});
			},

			/**
			 * Handle form submission
			 */
			handleSubmit() {
				this.$v.account.$touch();
				this.$v.termsAccepted.$touch();

				if (this.$v.account.$error || this.$v.termsAccepted.$error) {
					return;
				}

				axios.post('api/v2/create-account', { data: this.account, accreditated: true })
					.then(response => {
						if (response.data.status === 'error') {
							alert('There was an error with your submission. Please double check your data and try again.');
							this.formErrors = response.data.errors;
						} else {
							this.$emit('next');
						}
					})
					.catch(e => {console.log(e)});
			}
		},

		created() {
			this.getCountries();
		}
	}
</script>