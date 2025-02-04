import { ContactInformationFormWrapper } from './ContactInformationFormWrapper';
import { useContactInformationFormMeta } from './contactInformationFormMeta';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import {
    useIsCustomerUserRegisteredQueryApi,
    usePrivacyPolicyArticleUrlQueryApi,
    useTermsAndConditionsArticleUrlQueryApi,
} from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import Trans from 'next-translate/Trans';
import { Dispatch, SetStateAction, useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { twJoin } from 'tailwind-merge';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';

type ContactInformationContentProps = {
    setIsLoginPopupOpened: Dispatch<SetStateAction<boolean>>;
};

export const ContactInformationContent: FC<ContactInformationContentProps> = ({ setIsLoginPopupOpened }) => {
    const { t } = useTranslation();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { formState } = formProviderMethods;

    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [{ data: termsAndConditionsArticleUrlData }] = useTermsAndConditionsArticleUrlQueryApi();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();

    const termsAndConditionsArticleUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;

    const [{ data: isCustomerUserRegisteredData }] = useIsCustomerUserRegisteredQueryApi({
        variables: {
            email: emailValue,
        },
        pause: !isEmailFilledCorrectly,
    });

    useEffect(() => {
        if (isUserLoggedIn) {
            setIsLoginPopupOpened(false);
        }
    }, [isUserLoggedIn]);

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.email.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                    onChange: () => updateContactInformation({ email: emailValue }),
                }}
            />

            {isCustomerUserRegisteredData?.isCustomerUserRegistered && !isUserLoggedIn && (
                <Button size="small" type="button" onClick={() => setIsLoginPopupOpened(true)} className="mb-5">
                    {t('User with this email is already registered. Do you want to sign in')}
                </Button>
            )}

            {isEmailFilledCorrectly && <ContactInformationFormWrapper />}

            <div className={twJoin(!isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
                <p className="mb-4">
                    <Trans
                        i18nKey="ContactInformationInfo"
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        components={{
                            lnk1:
                                termsAndConditionsArticleUrl !== undefined ? (
                                    <Link href={termsAndConditionsArticleUrl} isExternal target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                            lnk2:
                                privacyPolicyArticleUrl !== undefined ? (
                                    <Link href={privacyPolicyArticleUrl} isExternal target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                        }}
                    />
                </p>

                <CheckboxControlled
                    name={formMeta.fields.newsletterSubscription.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.newsletterSubscription.label,
                    }}
                />
            </div>
        </>
    );
};
