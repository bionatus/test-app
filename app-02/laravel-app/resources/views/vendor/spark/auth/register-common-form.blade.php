<form role="form">
    @if (Spark::usesTeams() && Spark::onlyTeamPlans())
        <!-- Team Name -->
        <div class="form-group row" v-if=" ! invitation">
            <label class="col-md-4 col-form-label text-md-right">{{ __('teams.team_name') }}</label>

            <div class="col-md-6">
                <input type="text" class="form-control" name="team" v-model="registerForm.team" :class="{'is-invalid': registerForm.errors.has('team')}" autofocus>

                <span class="invalid-feedback" v-show="registerForm.errors.has('team')">
                    @{{ registerForm.errors.get('team') }}
                </span>
            </div>
        </div>

        @if (Spark::teamsIdentifiedByPath())
            <!-- Team Slug (Only Shown When Using Paths For Teams) -->
            <div class="form-group row" v-if=" ! invitation">
                <label class="col-md-4 col-form-label text-md-right">{{ __('teams.team_slug') }}</label>

                <div class="col-md-6">
                    <input type="text" class="form-control" name="team_slug" v-model="registerForm.team_slug" :class="{'is-invalid': registerForm.errors.has('team_slug')}" autofocus>

                    <small class="form-text text-muted" v-show="! registerForm.errors.has('team_slug')">
                        {{__('teams.slug_input_explanation')}}
                    </small>

                    <span class="invalid-feedback" v-show="registerForm.errors.has('team_slug')">
                        @{{ registerForm.errors.get('team_slug') }}
                    </span>
                </div>
            </div>
        @endif
    @endif

    <!-- First Name -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">First Name</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="first_name"
                placeholder="First Name"
                v-model="registerForm.first_name"
                :class="{'is-invalid': registerForm.errors.has('first_name')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('first_name')">
                @{{ registerForm.errors.get('first_name') }}
            </span>
        </div>
    </div>

    <!-- Last Name -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Last Name</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="last_name"
                placeholder="Last Name"
                v-model="registerForm.last_name"
                :class="{'is-invalid': registerForm.errors.has('last_name')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('last_name')">
                @{{ registerForm.errors.get('last_name') }}
            </span>
        </div>
    </div>

    <!-- E-Mail Address -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">E-Mail</label>

        <div class="col-md-6">
            <input type="email" class="form-control" name="email"
                placeholder="Email"
                v-model="registerForm.email"
                :class="{'is-invalid': registerForm.errors.has('email')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('email')">
                @{{ registerForm.errors.get('email') }}
            </span>
        </div>
    </div>

     <!-- Phone -->
     <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Mobile Phone</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="phone"
                placeholder="Mobile Phone"
                v-model="registerForm.phone"
                :class="{'is-invalid': registerForm.errors.has('phone')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('phone')">
                @{{ registerForm.errors.get('phone') }}
            </span>
        </div>
    </div>

    <!-- Password -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Password</label>

        <div class="col-md-6">
            <input type="password" class="form-control" name="password"
                placeholder="Password"
                v-model="registerForm.password"
                :class="{'is-invalid': registerForm.errors.has('password')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('password')">
                @{{ registerForm.errors.get('password') }}
            </span>
        </div>
    </div>

    <!-- Password Confirmation -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Repeat Password</label>

        <div class="col-md-6">
            <input type="password" class="form-control" name="password_confirmation"
                placeholder="Repeat Password"
                v-model="registerForm.password_confirmation"
                :class="{'is-invalid': registerForm.errors.has('password_confirmation')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('password_confirmation')">
                @{{ registerForm.errors.get('password_confirmation') }}
            </span>
        </div>
    </div>

     <!-- Company -->
     <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Company</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="company"
                placeholder="Company"
                v-model="registerForm.company"
                :class="{'is-invalid': registerForm.errors.has('company')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('company')">
                @{{ registerForm.errors.get('company') }}
            </span>
        </div>
    </div>

    <!-- HVAC Supplier -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Who's your HVAC supplier?</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="hvac_supplier"
                placeholder="Who's your HVAC supplier?"
                v-model="registerForm.hvac_supplier"
                :class="{'is-invalid': registerForm.errors.has('hvac_supplier')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('hvac_supplier')">
                @{{ registerForm.errors.get('hvac_supplier') }}
            </span>
        </div>
    </div>

    <!-- Occupation -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">I am a:</label>

        <div class="col-md-6">
            <select class="form-control" name="occupation"
                v-model="registerForm.occupation"
                :class="{'is-invalid': registerForm.errors.has('occupation')}">

                <option value="technician">Technician</option>
                <option value="service-manager">Service Manager</option>
                <option value="sales-person">Sales Person</option>
                <option value="owner">Business Owner</option>
                <option value="supplier">HVAC Supplier</option>
                <option value="other">Other</option>
            </select>
            <span class="invalid-feedback" v-show="registerForm.errors.has('occupation')">
                @{{ registerForm.errors.get('occupation') }}
            </span>
        </div>
    </div>

    <!-- Type of Services -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">We mostly do:</label>

        <div class="col-md-6">
            <select class="form-control" name="type_of_services"
                v-model="registerForm.type_of_services"
                :class="{'is-invalid': registerForm.errors.has('type_of_services')}">

                <option value="commercial">Commercial</option>
                <option value="residential">Residential</option>
                <option value="other">Other/NA</option>
            </select>
            <span class="invalid-feedback" v-show="registerForm.errors.has('type_of_services')">
                @{{ registerForm.errors.get('type_of_services') }}
            </span>
        </div>
    </div>

     <!-- References -->
     <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">How did you hear about Bluon?</label>

        <div class="col-md-6">
            <select class="form-control" name="references"
                v-model="registerForm.references"
                :class="{'is-invalid': registerForm.errors.has('references')}">

                <option value="facebook">Facebook</option>
                <option value="youtube">YouTube</option>
                <option value="linkedin">LinkedIn</option>
                <option value="web-search">Web Search</option>
                <option value="co-worker">Co-worker</option>
                <option value="group-training">Group Training</option>
                <option value="tradeshow">Tradeshow</option>
                <option value="supply-house">Supply House</option>
                <option value="customer">Customer</option>
                <option value="friend-family">Friend/Family</option>
                <option value="other">Other</option>
            </select>
            <span class="invalid-feedback" v-show="registerForm.errors.has('references')">
                @{{ registerForm.errors.get('references') }}
            </span>
        </div>
    </div>

     <!-- Address -->
     <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Mailing Address</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="address"
                placeholder="Mailing Address"
                v-model="registerForm.address"
                :class="{'is-invalid': registerForm.errors.has('address')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('address')">
                @{{ registerForm.errors.get('address') }}
            </span>
        </div>
    </div>

    <!-- City -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">City</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="city"
                placeholder="City"
                v-model="registerForm.city"
                :class="{'is-invalid': registerForm.errors.has('city')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('city')">
                @{{ registerForm.errors.get('city') }}
            </span>
        </div>
    </div>

     <!-- State -->
     <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">State/Province</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="state"
                placeholder="State/Province"
                v-model="registerForm.state"
                :class="{'is-invalid': registerForm.errors.has('state')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('state')">
                @{{ registerForm.errors.get('state') }}
            </span>
        </div>
    </div>

    <!-- ZIP -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Postal Code</label>

        <div class="col-md-6">
            <input type="text" class="form-control" name="zip"
                placeholder="Postal Code"
                v-model="registerForm.zip"
                :class="{'is-invalid': registerForm.errors.has('zip')}">

            <span class="invalid-feedback" v-show="registerForm.errors.has('zip')">
                @{{ registerForm.errors.get('zip') }}
            </span>
        </div>
    </div>

    <!-- Country -->
    <div class="form-group row">
        <label class="col-md-4 col-form-label text-md-right">Country</label>

        <div class="col-md-6">
            <select class="form-control" name="country"
                v-model="registerForm.country"
                :class="{'is-invalid': registerForm.errors.has('country')}">

                @foreach (app(Laravel\Spark\Repositories\Geography\CountryRepository::class)->all() as $key => $country)
                    <option value="{{ $key }}">{{ $country }}</option>
                @endforeach
            </select>

            <span class="invalid-feedback" v-show="registerForm.errors.has('country')">
                @{{ registerForm.errors.get('country') }}
            </span>
        </div>
    </div>

    <!-- Terms And Conditions -->
    <div v-if=" ! selectedPlan || selectedPlan.price == 0">
        <div class="form-group row">
            <div class="col-md-6 offset-md-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="terms" :class="{'is-invalid': registerForm.errors.has('terms')}" v-model="registerForm.terms">
                    <label class="form-check-label" for="terms">
                        {!! __('I Accept :linkOpen The Terms Of Service :linkClose', ['linkOpen' => '<a href="/terms" target="_blank">', 'linkClose' => '</a>']) !!}
                    </label>
                    <div class="invalid-feedback" v-show="registerForm.errors.has('terms')">
                        <strong>@{{ registerForm.errors.get('terms') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <div class="col-md-6 offset-md-4">
                <button class="btn btn-primary" @click.prevent="register" :disabled="registerForm.busy">
                    <span v-if="registerForm.busy">
                        <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Registering')}}
                    </span>

                    <span v-else>
                        <i class="fa fa-btn fa-check-circle"></i> {{__('Register')}}
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
