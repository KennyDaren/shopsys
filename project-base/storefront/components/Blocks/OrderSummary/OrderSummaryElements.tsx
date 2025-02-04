export const OrderSummaryContent: FC = ({ children }) => <div className="relative flex flex-col">{children}</div>;

export const OrderSummaryRowWrapper: FC = ({ children, dataTestId }) => (
    <div className="mb-5 border-b border-creamWhite pb-3" data-testid={dataTestId}>
        {children}
    </div>
);

export const OrderSummaryRow: FC = ({ children }) => <div className="flex justify-between">{children}</div>;

export const OrderSummaryTextAndImage: FC = ({ children, dataTestId }) => (
    <div className="flex items-center gap-2 align-baseline text-sm" data-testid={dataTestId}>
        {children}
    </div>
);

export const OrderSummaryPrice: FC = ({ children, dataTestId }) => (
    <div className="text-sm" data-testid={dataTestId}>
        {children}
    </div>
);
