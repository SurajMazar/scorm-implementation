import {useContext} from "react";
import {ScormContext} from "../../container/context/ScormContext";


const useScormContext = () => {
    return useContext(ScormContext)
}

export default useScormContext
