import axiosClient from '@/axios';

export const SubjectService = {
    async getSubjects() {
        const response = await axiosClient.get('/subjects');
        return response.data.data;
    },
    async createSubject(subjectData) {
        const response = await axiosClient.post('/subjects', subjectData);
        return response.data;
    },
    async updateSubject(id, subjectData) {
        const response = await axiosClient.put(`/subjects/${id}`, subjectData);
        return response.data;
    },
    async deleteSubject(id) {
        const response = await axiosClient.delete(`/subjects/${id}`);
        return response.data;
    }
};
