import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ArrowIcon, SpinnerIcon } from 'components/Basic/Icon/IconsSvg';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { useRouter } from 'next/router';
import { twJoin } from 'tailwind-merge';

type OrderActionProps = {
    buttonBack: string;
    buttonNext: string;
    buttonBackLink: string;
    buttonNextLink?: string;
    hasDisabledLook: boolean;
    withGapBottom?: boolean;
    withGapTop?: boolean;
    nextStepClickHandler?: () => void;
    isLoading?: boolean;
};

const TEST_IDENTIFIER = 'blocks-orderaction-';

export const OrderAction: FC<OrderActionProps> = ({
    buttonBack,
    buttonNext,
    buttonBackLink,
    buttonNextLink,
    hasDisabledLook,
    nextStepClickHandler,
    withGapBottom,
    withGapTop,
    isLoading,
}) => {
    const router = useRouter();

    const onNextStepHandler = () => {
        if (buttonNextLink !== undefined) {
            router.push(buttonNextLink, undefined);
        }
        if (nextStepClickHandler !== undefined) {
            nextStepClickHandler();
        }
    };

    return (
        <div
            className={twJoin(
                'flex flex-col flex-wrap items-center lg:w-full lg:flex-row lg:justify-between ',
                withGapBottom && 'mb-12 lg:mb-24',
                withGapTop && 'mt-8',
            )}
        >
            <div className="order-2 lg:order-1" data-testid={TEST_IDENTIFIER + 'back'}>
                <ExtendedNextLink
                    href={buttonBackLink}
                    type="static"
                    className="font-bold uppercase text-dark no-underline"
                >
                    <>
                        <ArrowIcon className="relative top-0 mr-1 rotate-90 text-greyLight" />
                        {buttonBack}
                    </>
                </ExtendedNextLink>
            </div>
            <div className="order-1 mb-8 w-auto lg:order-2 lg:mb-0" data-testid={TEST_IDENTIFIER + 'next'}>
                <SubmitButton variant="primary" isWithDisabledLook={hasDisabledLook} onClick={onNextStepHandler}>
                    {isLoading && <SpinnerIcon className="w-5" />}
                    <span>{buttonNext}</span>
                    <ArrowIcon className="relative top-0 ml-1 -rotate-90 text-white" />
                </SubmitButton>
            </div>
        </div>
    );
};
