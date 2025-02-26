export default {
    // ...existing code...
    methods: {
        fetchQuestions(quizId) {
            axios.get(`/api/quizzes/${quizId}/questions`)
                .then(response => {
                    this.questions = response.data;
                })
                .catch(error => {
                    console.error(error);
                });
        },
        // ...existing code...
    },
    // ...existing code...
}
