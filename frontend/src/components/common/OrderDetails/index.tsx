import {Anchor, Tooltip} from "@mantine/core";
import {prettyDate, relativeDate} from "../../../utilites/dates.ts";
import {OrderStatusBadge} from "../OrderStatusBadge";
import {Currency} from "../Currency";
import {Card} from "../Card";
import {Order, Event} from "../../../types.ts";
import classes from "./OrderDetails.module.scss";

export const OrderDetails = ({order, event}: {order: Order, event: Event}) => {
    return (
        <Card className={classes.orderDetails} variant={'lightGray'}>
            <div className={classes.block}>
                <div className={classes.title}>
                    Name
                </div>
                <div className={classes.amount}>
                    {order.first_name} {order.last_name}
                </div>
            </div>
            <div className={classes.block}>
                <div className={classes.title}>
                    Email
                </div>
                <div className={classes.value}>
                    <Anchor href={'mailto:' + order.email} target={'_blank'}>{order.email}</Anchor>
                </div>
            </div>
            <div className={classes.block}>
                <div className={classes.title}>
                    Order Date
                </div>
                <div className={classes.amount}>
                    <Tooltip label={prettyDate(order.created_at, event.timezone)} position={'bottom'} withArrow>
                            <span>
                                {relativeDate(order.created_at)}
                            </span>
                    </Tooltip>
                </div>
            </div>
            <div className={classes.block}>
                <div className={classes.title}>
                    Status
                </div>
                <div className={classes.amount}>
                    <OrderStatusBadge order={order} variant={'filled'}/>
                </div>
            </div>
            <div className={classes.block}>
                <div className={classes.title}>
                    Total order amount
                </div>
                <div className={classes.amount}>
                    <Currency currency={order.currency} price={order.total_gross}/>
                </div>
            </div>
            <div className={classes.block}>
                <div className={classes.title}>
                    Total refunded
                </div>
                <div className={classes.amount}>
                    <Currency currency={order.currency} price={order.total_refunded}/>
                </div>
            </div>
        </Card>
    );
}