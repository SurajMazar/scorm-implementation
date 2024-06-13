import {QueryClient, QueryClientProvider} from 'react-query'
import {ReactQueryDevtools} from "react-query/devtools";

const ReactQueryWrapper = ({children}) => {
    /**
     * REACT QUERY CLIENT
     */
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                refetchOnWindowFocus: false
            }
        }
    })

    return (
        <QueryClientProvider client={queryClient}>
            {children}
            {/*REACT QUERY DEV TOOLS*/}
            <ReactQueryDevtools initialIsOpen={false}/>
        </QueryClientProvider>
    );
}


export default ReactQueryWrapper
