import axios from "axios";

class ScormService {
    /**
     *
     * @param identifier
     * @param trackingId
     * @returns {Promise<*|null>}
     */
    static async getDetails(identifier,trackingId) {
        try {
            const response = await axios.get(`/api/v1/public/scorm/details/${identifier}?tracking_id=${trackingId}`)
            return response?.data?.data || null;
        } catch (exception) {
            throw exception;
        }
    }

    /**
     *
     * @param scoUUID
     * @param version
     * @param data
     * @param trackingId
     * @returns {Promise<*|null>}
     */
    static async trackProgress(scoUUID, version, data, trackingId) {
        try {
            const response = await axios.post(`/api/v1/public/scorm/progress-tracking?tracking_id=${trackingId}`, {
                'sco_uuid': scoUUID,
                'version': version,
                'cmi': data
            })
            return response?.data?.data || null;
        } catch (exception) {
            throw exception;
        }
    }
}

export default ScormService
