import {Navigate, Outlet} from "react-router-dom";
import classes from "./Auth.module.scss";
import {t} from "@lingui/macro";
import {useGetMe} from "../../../queries/useGetMe.ts";

export const AuthLayout = () => {
    const me = useGetMe();
    if (me.isSuccess) {
        return <Navigate to={'/manage/events'}/>
    }

    return (
        <main className={classes.container}>
            <div className={classes.logo}>
                <img src={'/logo.svg'} alt={t`hi.events logo`}/>
            </div>
            <div className={classes.wrapper}>
                <Outlet/>
            </div>
        </main>
    );
};