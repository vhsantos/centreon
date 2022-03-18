import * as React from 'react';

import { useRequest } from '@centreon/ui';

import { getOpenidConfiguration } from '../api';
import { openidConfigurationDecoder } from '../api/decoders';

import { OpenidConfiguration } from './models';

interface UseOpenidState {
  initialOpenidConfiguration: OpenidConfiguration | null;
  loadOpenidConfiguration: () => void;
  sendingGetOpenidConfiguration: boolean;
}

const useOpenid = (): UseOpenidState => {
  const [initialOpenidConfiguration, setInitialOpenidConfiguration] =
    React.useState<OpenidConfiguration | null>(null);
  const { sendRequest, sending } = useRequest<OpenidConfiguration>({
    decoder: openidConfigurationDecoder,
    request: getOpenidConfiguration,
  });

  const loadOpenidConfiguration = (): void => {
    sendRequest()
      .then(setInitialOpenidConfiguration)
      .catch(() => undefined);
  };

  return {
    initialOpenidConfiguration,
    loadOpenidConfiguration,
    sendingGetOpenidConfiguration: sending,
  };
};

export default useOpenid;
