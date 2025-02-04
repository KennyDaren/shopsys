import { gtmSafePushEvent } from 'gtm/helpers/gtm';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { GtmPageViewEventType } from 'gtm/types/events';

export const useGtmPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType, fetching?: boolean): void => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const lastViewedSlug = useRef<string>();

    useEffect(() => {
        if (gtmPageViewEvent._isLoaded && lastViewedSlug.current !== slug && !fetching) {
            lastViewedSlug.current = slug;
            gtmSafePushEvent(gtmPageViewEvent);
        }
    }, [gtmPageViewEvent, fetching, slug]);
};
