import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { TextField, useMemoComponent } from '@centreon/ui';

import {
  labelPasswordCasePolicy,
  labelPasswordLength,
} from '../../translatedLabels';
import { getField } from '../utils';

import CaseButtons from './CaseButtons';

const passwordMinLengthFieldName = 'passwordMinLength';

const useStyles = makeStyles((theme) => ({
  fields: {
    alignItems: 'center',
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    marginTop: theme.spacing(1),
  },
  passwordLengthInput: {
    width: '75%',
  },
}));

const PasswordCasePolicy = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { handleChange, errors, values } = useFormikContext<FormikValues>();

  const passwordLengthError = getField<string | undefined>({
    field: passwordMinLengthFieldName,
    object: errors,
  });

  const passwordLengthValue = getField<number>({
    field: passwordMinLengthFieldName,
    object: values,
  });

  return useMemoComponent({
    Component: (
      <div>
        <Typography variant="h5">{t(labelPasswordCasePolicy)}</Typography>
        <div className={classes.fields}>
          <TextField
            required
            className={classes.passwordLengthInput}
            error={passwordLengthError}
            helperText={passwordLengthError}
            inputProps={{
              'aria-label': t(labelPasswordLength),
              min: 0,
            }}
            label={t(labelPasswordLength)}
            name={passwordMinLengthFieldName}
            type="number"
            value={passwordLengthValue}
            onChange={handleChange(passwordMinLengthFieldName)}
          />
          <CaseButtons />
        </div>
      </div>
    ),
    memoProps: [passwordLengthError, passwordLengthValue],
  });
};

export default PasswordCasePolicy;
