<template>
	<div class="form-step-two">
		<div class="form__content">
			<div class="form__shell">
				<div class="form__title">
					<h1>{{ title }}</h1>

					<p>{{ text }}</p>
				</div>

				<div class="form__section">
					<div class="form__video">
						<iframe width="560" height="315" :src="video" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>

					<div class="form__list">
						
						<div 
							class="form__list-item"
							v-for="(question, index) in questions"
							:key="index"
						>

							<p>{{question.title}} <a href="#" v-if="question.chart" class="chart-open" @click.prevent="openChart = true">Open PT Chart</a></p>

							<div class="form__options">
								<div 
									class="form__controls"
									v-for="(option, optionIndex) in question.options"
									:key="optionIndex"
									:class="{ 'correct' : answeredQuestions[question.index] && answeredQuestions[question.index].answer == optionIndex && answeredQuestions[question.index].correct }"
								>
									<input 
										type="radio" 
										:name="`q${question.index}`" 
										:id="`q${question.index}a${optionIndex}`" 
										:value="option"
										@change="handleRadioChange(question.index, optionIndex)"
									>
									
									<label :for="`q${question.index}a${optionIndex}`">{{ option }}</label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="form__actions">
			<div class="form__shell">
				<a href="#" class="btn-form btn-form--back" @click.prevent="handlePrevClick">&larr; Back</a>

				<a 
					href="#" 
					class="btn-form" 
					:class="{ 'disabled' : !isValid }" 
					@click.prevent="handleNextClick"
					v-html="nextButtonText"
				></a>
			</div>
		</div>

		<div class="form__chart" :class="{ 'active' : openChart }">
			<div class="form__chart-wrapper">
				<a href="#" class="form__chart-close" @click.prevent="openChart = false;"></a>

				<img src="images/pt-chart.png" alt="">
			</div><!-- /.form__chart-wrapper -->
		</div><!-- /.form__chart -->

	</div>
</template>
<script>
	
	export default {
		props: {
			value: {
				type: Array,
				required: true
			},
			title: {
				type: String,
				required: true
			},
			text: {
				type: String,
				required: true
			},
			video: {
				type: String,
				required: true
			},
			offset: {
				type: String,
				required: true
			},
			nextButtonText: {
				type: String,
				required: true
			}
		},
		data() {
			return {
				numberQuestions: 10,
				questions: [],
				answeredQuestions: {},
				isValid: false,
				openChart: false,
			}
		},
		methods: {
			setQuestions() {
				const offset = parseInt(this.offset);
				this.questions = this.value.slice(offset, offset + this.numberQuestions);
			},

			handlePrevClick() {
				this.$emit('prev');	
			},

			handleNextClick() {
				if (!this.isValid) {
					return;
				}

				this.$emit('next');
			},

			handleRadioChange(questionIndex, answerIndex) {
				const currentQuestion = this.questions.find( question => question.index === questionIndex );

				Vue.set(this.answeredQuestions, questionIndex, {
					answer: answerIndex,
					correct: currentQuestion.correctIndex == answerIndex ? true : false,
				});

				this.validate();

			},

			validate() {
				if (Object.keys(this.answeredQuestions).length < this.numberQuestions) {
					this.isValid = false;
					return;
				} 

				for (const index in this.answeredQuestions) {
					if (this.answeredQuestions[index].correct === false) {
						this.isValid = false;
						return;
					}
				}

				this.isValid = true;
			},
		},
		mounted() {
			this.setQuestions();
		}
	}
</script>