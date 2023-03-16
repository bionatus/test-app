<update-profile-details :user="user" inline-template>
    <div class="card">
        <div class="card-header">Account Details</div>

        <div class="card-body">
            <!-- Success Message -->
            <div class="alert alert-success" v-if="form.successful">
                Your profile has been updated!
            </div>

            <form role="form">
                <!-- First Name -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">First Name</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="first_name"
                               v-model="form.first_name"
                               :class="{'is-invalid': form.errors.has('first_name')}">

                        <span class="invalid-feedback" v-show="form.errors.has('first_name')">
                            @{{ form.errors.get('first_name') }}
                        </span>
                    </div>
                </div>

                 <!-- Last Name -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Last Name</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="last_name"
                               v-model="form.last_name"
                               :class="{'is-invalid': form.errors.has('last_name')}">

                        <span class="invalid-feedback" v-show="form.errors.has('last_name')">
                            @{{ form.errors.get('last_name') }}
                        </span>
                    </div>
                </div>

                 <!-- Email -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Email</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="email"
                               v-model="form.email"
                               :class="{'is-invalid': form.errors.has('email')}">

                        <span class="invalid-feedback" v-show="form.errors.has('email')">
                            @{{ form.errors.get('email') }}
                        </span>
                    </div>
                </div>

                 <!-- Password -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Password</label>

                    <div class="col-md-6">
                        <input type="password" class="form-control" name="password"
                               v-model="form.password"
                               :class="{'is-invalid': form.errors.has('password')}">

                        <span class="invalid-feedback" v-show="form.errors.has('password')">
                            @{{ form.errors.get('password') }}
                        </span>
                    </div>
                </div>

                <!-- Repeat Password -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Repeat Password</label>

                    <div class="col-md-6">
                        <input type="password" class="form-control" name="repeat_password"
                               v-model="form.repeat_password"
                               :class="{'is-invalid': form.errors.has('repeat_password')}">

                        <span class="invalid-feedback" v-show="form.errors.has('repeat_password')">
                            @{{ form.errors.get('repeat_password') }}
                        </span>
                    </div>
                </div>

                <!-- Company -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Company</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="company"
                               v-model="form.company"
                               :class="{'is-invalid': form.errors.has('company')}">

                        <span class="invalid-feedback" v-show="form.errors.has('company')">
                            @{{ form.errors.get('company') }}
                        </span>
                    </div>
                </div>

                 <!-- Supplier -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Who's your HVAC supplier?</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="hvac_supplier"
                               v-model="form.hvac_supplier"
                               :class="{'is-invalid': form.errors.has('hvac_supplier')}">

                        <span class="invalid-feedback" v-show="form.errors.has('hvac_supplier')">
                            @{{ form.errors.get('hvac_supplier') }}
                        </span>
                    </div>
                </div>

                 <!-- Occupation -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">I am a:</label>

                    <div class="col-md-6">
                        <select name="occupation" class="form-control"
                            v-model="form.occupation"
                            :class="{'is-invalid': form.errors.has('occupation')}">
                        >
                            <option value="technician">Technician</option>
                            <option value="service-manager">Service Manager</option>
                            <option value="sales-person">Sales Person</option>
                            <option value="owner">Business Owner</option>
                            <option value="supplier">HVAC Supplier</option>
                            <option value="other">Other/NA</option>
                        </select>

                        <span class="invalid-feedback" v-show="form.errors.has('hvac_supplier')">
                            @{{ form.errors.get('hvac_supplier') }}
                        </span>
                    </div>
                </div>

                 <!-- Type of Services -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">We mostly do:</label>

                    <div class="col-md-6">
                        <select name="type_of_services" class="form-control"
                            v-model="form.type_of_services"
                            :class="{'is-invalid': form.errors.has('type_of_services')}">
                        >
                            <option value="commercial">Commercial</option>
                            <option value="residential">Residential</option>
                            <option value="other">Other/NA</option>
                        </select>

                        <span class="invalid-feedback" v-show="form.errors.has('type_of_services')">
                            @{{ form.errors.get('hvac_supplier') }}
                        </span>
                    </div>
                </div>

                <!-- References -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">How did you hear about Bluon:</label>

                    <div class="col-md-6">
                        <select name="references" class="form-control"
                            v-model="form.references"
                            :class="{'is-invalid': form.errors.has('references')}">
                        >
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

                        <span class="invalid-feedback" v-show="form.errors.has('references')">
                            @{{ form.errors.get('hvac_supplier') }}
                        </span>
                    </div>
                </div>

                <!-- Phone -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Phone</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="phone"
                               v-model="form.phone"
                               :class="{'is-invalid': form.errors.has('phone')}">

                        <span class="invalid-feedback" v-show="form.errors.has('phone')">
                            @{{ form.errors.get('phone') }}
                        </span>
                    </div>
                </div>

                 <!-- Address -->
                 <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Mailing Address</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="address"
                               v-model="form.address"
                               :class="{'is-invalid': form.errors.has('address')}">

                        <span class="invalid-feedback" v-show="form.errors.has('address')">
                            @{{ form.errors.get('address') }}
                        </span>
                    </div>
                </div>

                <!-- City -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">City</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="city"
                               v-model="form.city"
                               :class="{'is-invalid': form.errors.has('city')}">

                        <span class="invalid-feedback" v-show="form.errors.has('city')">
                            @{{ form.errors.get('city') }}
                        </span>
                    </div>
                </div>

                <!-- State -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">State/Province</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="state"
                               v-model="form.state"
                               :class="{'is-invalid': form.errors.has('state')}">

                        <span class="invalid-feedback" v-show="form.errors.has('state')">
                            @{{ form.errors.get('state') }}
                        </span>
                    </div>
                </div>

                <!-- ZIP -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Postal Code</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="zip"
                               v-model="form.zip"
                               :class="{'is-invalid': form.errors.has('zip')}">

                        <span class="invalid-feedback" v-show="form.errors.has('zip')">
                            @{{ form.errors.get('zip') }}
                        </span>
                    </div>
                </div>

                <!-- Country -->
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right">Country</label>

                    <div class="col-md-6">
                        <input type="text" class="form-control" name="country"
                               v-model="form.country"
                               :class="{'is-invalid': form.errors.has('country')}">

                        <span class="invalid-feedback" v-show="form.errors.has('country')">
                            @{{ form.errors.get('country') }}
                        </span>
                    </div>
                </div>


                <!-- Update Button -->
                <div class="form-group">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary"
                                @click.prevent="update"
                                :disabled="form.busy">

                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</update-profile-details>