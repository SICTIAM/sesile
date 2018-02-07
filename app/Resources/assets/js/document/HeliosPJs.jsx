import React, { Component } from 'react'
import {func, object, array} from 'prop-types'
import { translate } from 'react-i18next'
import {Select} from '../_components/Form'

class HeliosPJs extends Component {

    static contextTypes = { t: func }

    render () {
        const { t } = this.context
        const { pjs, handleClickPJ } = this.props

        return (
            <div>
                <div className="grid-x">
                    <div className="cell medium-12">
                        <h2>{ t('common.helios.attachment_list') }</h2>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>{ t('common.helios.attachment_number') }</th>
                            <th>{ t('common.helios.creditor') }</th>
                            <th>{ t('common.helios.expenditure_type') }</th>
                            <th>{ t('common.helios.imputation') }</th>
                            <th>{ t('common.helios.amount_ht') }</th>
                            <th>{ t('common.helios.amount_tva') }</th>
                            <th>{ t('common.helios.amount_ttc') }</th>
                            <th>{ t('common.helios.attachments') }</th>
                        </tr>
                    </thead>
                    <tbody>
                    {
                        pjs.map(pj =>
                            <tr key={ pj.id }>
                                <td>{ pj.id }</td>
                                <td>{ pj.civilite + " " + pj.prenom + " " + pj.nom }</td>
                                <td>{ pj.objet }</td>
                                <td>
                                    <Select id="imputations">
                                        { pj.imputations.map(imputation => <option key={imputation} value={imputation}>{imputation}</option>)}
                                    </Select>
                                </td>
                                <td>{ pj.mt_h_t }</td>
                                <td>{ pj.mt_t_v_a }</td>
                                <td>{ pj.mt_t_t_c }</td>
                                <td>
                                    { pj.liste_p_js.length > 0 &&
                                        <Select id="pj"
                                                onChange={handleClickPJ}
                                        >
                                            { pj.liste_p_js.map(liste_p_j => <option key={liste_p_j.id} value={liste_p_j.id}>{liste_p_j.nom}</option>)}
                                        </Select>
                                    }
                                </td>
                            </tr>
                        )
                    }
                    </tbody>
                </table>
            </div>
        )
    }
}

HeliosPJs.propsType = {
    pjs: array.isRequired,
    handleClickPJ: func
}

export default translate(['sesile'])(HeliosPJs)