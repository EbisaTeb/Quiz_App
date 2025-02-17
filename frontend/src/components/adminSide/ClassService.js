
import axiosClient from '@/axios';
export const ClassService = {
    getClasses() {
        return axiosClient.get('/classes').then((res) => res.data);
    },

    createClass(classData) {
        return axiosClient.post('/classes', classData);
    },

    updateClass(id, classData) {
        return axiosClient.put(`/classes/${id}`, classData);
    },

    deleteClass(id) {
        return axiosClient.delete(`/classes/${id}`);
    }
};
