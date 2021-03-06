import React, { Component } from 'react'
import {func, object, array} from 'prop-types'
import { translate } from 'react-i18next'
import {Select} from '../_components/Form'

class HeliosPJs extends Component {

    static contextTypes = { t: func }

    render () {
        const { t } = this.context
        const { pjs, handleClickPJ, typePes } = this.props

        return (
            <div className="cell medium-12">
                <div className="grid-x grid-margin-x grid-padding-x">
                    <div className="cell medium-12">
                        <h3>
                            {t('common.helios.attachment_list')}
                        </h3>
                    </div>
                </div>

                <div className="grid-x" style={{margin: '5px'}}>
                    <div className="cell medium-12">
                        <table>
                        <thead>
                            <tr>
                                <th>{ t('common.helios.attachment_number') }</th>
                                <th>{ t('common.helios.creditor') }</th>
                                <th>{ t('common.helios.type_pjs') + ' ' + typePes }</th>
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
                                        <Select
                                            id="imputations"
                                            onChange={() => {}}
                                            style={{
                                                fontSize: '1.15em',
                                                height: '20px',
                                                width: '63px',
                                                margin: '0',
                                                paddingBottom: '0',
                                                paddingTop: '0'
                                            }}>
                                            <option>{"Liste"}</option>
                                            { pj.imputations.map(imputation => <option key={imputation} value={imputation}>{imputation}</option>)}
                                        </Select>
                                    </td>
                                    <td>{ pj.mt_h_t }</td>
                                    <td>{ pj.mt_t_v_a }</td>
                                    <td>{ pj.mt_t_t_c }</td>
                                    <td>
                                        {pj.liste_p_js.length > 0 ?
                                            <Select
                                                id="pj"
                                                onChange={handleClickPJ}
                                                style={{
                                                    fontSize: '1.15em',
                                                    height: '20px',
                                                    width: '78px',
                                                    margin: '0',
                                                    paddingBottom: '0',
                                                    paddingTop: '0'
                                                }}>
                                                <option>{"Liste PJ"}</option>
                                                { pj.liste_p_js.map(liste_p_j => <option key={liste_p_j.id} value={liste_p_j.id}>{liste_p_j.nom}</option>)}
                                            </Select> :
                                            <p>{"Aucune"}</p>}
                                    </td>
                                </tr>
                            )
                        }
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        )
    }
}

HeliosPJs.propsType = {
    pjs: array.isRequired,
    handleClickPJ: func
}

export default translate(['sesile'])(HeliosPJs)