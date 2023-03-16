Vue.component('update-profile-details', {
    props: ['user'],

    data() {
        return {
            form: new SparkForm({
                first_name: '',
                last_name: '',
                email: '',
                password: '',
                repeat_password: '',
                company: '',
                hvac_supplier: '',
                occupation: '',
                type_of_service: '',
                references: '',
                phone: '',
                address: '',
                city: '',
                state: '',
                zip: '',
                country: '',
            })
        };
    },

    mounted() {
        this.form.first_name = this.user.first_name;
        this.form.last_name = this.user.last_name;
        this.form.email = this.user.email;
        this.form.password = this.user.password;
        this.form.repeat_password = this.user.repeat_password;
        this.form.company = this.user.company;
        this.form.hvac_supplier = this.user.hvac_supplier;
        this.form.occupation = this.user.occupation;
        this.form.type_of_services = this.user.type_of_services;
        this.form.references = this.user.references;
        this.form.phone = this.user.phone;
        this.form.address = this.user.address;
        this.form.city = this.user.city;
        this.form.state = this.user.state;
        this.form.zip = this.user.zip;
        this.form.country = this.user.country;
    },

    methods: {
        update() {
            Spark.put('/settings/profile/details', this.form)
                .then(response => {
                    Bus.$emit('updateUser');
                });
        }
    }
});